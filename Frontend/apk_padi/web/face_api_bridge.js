(function () {
  const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
  let modelPromise = null;

  function ensureOverlay() {
    let overlay = document.getElementById('padi-face-overlay');
    if (overlay) return overlay;

    overlay = document.createElement('div');
    overlay.id = 'padi-face-overlay';
    overlay.style.cssText = [
      'position:fixed',
      'inset:0',
      'z-index:99999',
      'display:none',
      'align-items:center',
      'justify-content:center',
      'background:rgba(2,44,34,.72)',
      'font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif'
    ].join(';');
    document.body.appendChild(overlay);
    return overlay;
  }

  function loadModels() {
    if (!window.faceapi) {
      return Promise.reject(new Error('Fitur wajah belum siap. Periksa internet lalu coba lagi.'));
    }

    if (!modelPromise) {
      modelPromise = Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
      ]);
    }

    return modelPromise;
  }

  async function openCameraOverlay(title, subtitle) {
    await loadModels();

    const overlay = ensureOverlay();
    overlay.innerHTML = [
      '<div style="width:min(92vw,380px);background:#fff;border-radius:20px;padding:18px;box-shadow:0 24px 80px rgba(0,0,0,.24);color:#052e25">',
      '<div style="font-size:18px;font-weight:800;margin-bottom:4px">' + title + '</div>',
      '<div id="padi-face-prompt" style="font-size:13px;color:#38665c;margin-bottom:12px">' + subtitle + '</div>',
      '<video autoplay muted playsinline style="width:100%;border-radius:16px;background:#ecfdf5;aspect-ratio:4/3;object-fit:cover"></video>',
      '<div style="height:8px;background:#dcfce7;border-radius:999px;margin-top:12px;overflow:hidden"><div id="padi-face-progress" style="height:100%;width:0%;background:#059669;border-radius:999px;transition:width .25s ease"></div></div>',
      '<div id="padi-face-status" style="font-size:13px;font-weight:700;color:#047857;margin-top:12px">Menyalakan kamera...</div>',
      '<button id="padi-face-cancel" style="margin-top:12px;width:100%;height:44px;border-radius:12px;border:1px solid #bbf7d0;background:#fff;color:#065f46;font-weight:800">Batal</button>',
      '</div>'
    ].join('');
    overlay.style.display = 'flex';

    const video = overlay.querySelector('video');
    const prompt = overlay.querySelector('#padi-face-prompt');
    const status = overlay.querySelector('#padi-face-status');
    const progress = overlay.querySelector('#padi-face-progress');
    const cancel = overlay.querySelector('#padi-face-cancel');
    let stream = null;
    let cancelled = false;

    cancel.onclick = function () {
      cancelled = true;
    };

    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
        audio: false
      });
      video.srcObject = stream;
      await video.play();

      return { overlay, video, prompt, status, progress, get cancelled() { return cancelled; }, stream };
    } catch (error) {
      overlay.style.display = 'none';
      throw error;
    }
  }

  function closeCameraOverlay(session) {
    if (session.stream) {
      session.stream.getTracks().forEach((track) => track.stop());
    }
    session.overlay.style.display = 'none';
  }

  function poseFromLandmarks(landmarks) {
    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();
    const nose = landmarks.getNose();
    const jaw = landmarks.getJawOutline();

    if (!leftEye.length || !rightEye.length || !nose.length || !jaw.length) {
      return { horizontal: 'unknown', vertical: 'unknown' };
    }

    const leftEyeX = leftEye.reduce((sum, point) => sum + point.x, 0) / leftEye.length;
    const rightEyeX = rightEye.reduce((sum, point) => sum + point.x, 0) / rightEye.length;
    const eyeCenterX = (leftEyeX + rightEyeX) / 2;
    const eyeDistance = Math.max(1, Math.abs(rightEyeX - leftEyeX));
    const noseTip = nose[Math.min(3, nose.length - 1)];
    const horizontalOffset = (noseTip.x - eyeCenterX) / eyeDistance;

    const jawTop = Math.min(...jaw.map((point) => point.y));
    const jawBottom = Math.max(...jaw.map((point) => point.y));
    const faceHeight = Math.max(1, jawBottom - jawTop);
    const verticalOffset = (noseTip.y - jawTop) / faceHeight;

    return {
      horizontal: horizontalOffset < -0.11 ? 'left' : horizontalOffset > 0.11 ? 'right' : 'front',
      vertical: verticalOffset < 0.46 ? 'up' : verticalOffset > 0.62 ? 'down' : 'middle'
    };
  }

  function poseMatches(detection, expectedPose) {
    if (expectedPose === 'any') return true;

    const pose = poseFromLandmarks(detection.landmarks);

    if (expectedPose === 'front') {
      return pose.horizontal === 'front';
    }

    if (expectedPose === 'left' || expectedPose === 'right') {
      return pose.horizontal === expectedPose;
    }

    if (expectedPose === 'up' || expectedPose === 'down') {
      return pose.vertical === expectedPose;
    }

    return false;
  }

  function descriptorDistance(a, b) {
    if (!a || !b || a.length !== b.length) return Number.POSITIVE_INFINITY;

    let sum = 0;
    for (let index = 0; index < a.length; index += 1) {
      const delta = a[index] - b[index];
      sum += delta * delta;
    }

    return Math.sqrt(sum);
  }

  function averageDescriptors(descriptors) {
    const avg = new Array(128).fill(0);
    descriptors.forEach((descriptor) => {
      descriptor.forEach((value, index) => {
        avg[index] += value;
      });
    });

    return avg.map((value) => value / descriptors.length);
  }

  async function readSingleDescriptor(session, step) {
    const promptText = typeof step === 'string' ? step : step.text;
    const expectedPose = typeof step === 'string' ? 'any' : step.pose;
    session.prompt.textContent = promptText;

    for (let attempt = 0; attempt < 36; attempt += 1) {
      if (session.cancelled) throw new Error('Pengambilan wajah dibatalkan.');
      session.status.textContent = 'Ikuti arahan, tahan sebentar...';
      const detection = await faceapi
        .detectSingleFace(session.video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.62 }))
        .withFaceLandmarks()
        .withFaceDescriptor();

      if (detection && detection.descriptor) {
        if (!poseMatches(detection, expectedPose)) {
          session.status.textContent = 'Wajah belum pas. Ikuti tulisan di atas.';
          await new Promise((resolve) => setTimeout(resolve, 260));
          continue;
        }

        session.status.textContent = 'Wajah berhasil terbaca.';
        return Array.from(detection.descriptor);
      }

      await new Promise((resolve) => setTimeout(resolve, 350));
    }

    throw new Error('Wajah belum terbaca. Dekatkan wajah dan cari cahaya lebih terang.');
  }

  async function readStableDescriptor(session, step, sampleCount) {
    const descriptors = [];

    while (descriptors.length < sampleCount) {
      const descriptor = await readSingleDescriptor(session, step);

      if (descriptors.length > 0 && descriptorDistance(descriptors[descriptors.length - 1], descriptor) > 0.32) {
        throw new Error('Wajah berubah saat dibaca. Coba ulang pelan-pelan.');
      }

      descriptors.push(descriptor);
      session.status.textContent = 'Tetap lihat kamera sebentar...';
      await new Promise((resolve) => setTimeout(resolve, 220));
    }

    return averageDescriptors(descriptors);
  }

  async function captureDescriptor() {
    const session = await openCameraOverlay(
      'Arahkan wajah ke kamera',
      'Pastikan cahaya terang dan hanya satu wajah di layar.'
    );

    try {
      const descriptor = await readStableDescriptor(session, {
        text: 'Lihat lurus ke kamera.',
        pose: 'front'
      }, 3);
      return {
        descriptor,
        label: 'Wajah berhasil terbaca'
      };
    } finally {
      closeCameraOverlay(session);
    }
  }

  async function captureEnrollment() {
    const session = await openCameraOverlay(
      'Daftarkan wajah',
      'Ikuti arahan singkat agar nanti masuk lebih mudah.'
    );

    const poses = [
      { text: 'Lihat lurus ke kamera.', pose: 'front' },
      { text: 'Putar wajah sedikit ke kanan.', pose: 'right' },
      { text: 'Putar wajah sedikit ke kiri.', pose: 'left' },
      { text: 'Kembali lihat lurus ke kamera.', pose: 'front' }
    ];

    try {
      const descriptors = [];

      for (let index = 0; index < poses.length; index += 1) {
        session.status.textContent = 'Langkah ' + (index + 1) + ' dari ' + poses.length;
        session.progress.style.width = Math.round((index / poses.length) * 100) + '%';
        descriptors.push(await readStableDescriptor(session, poses[index], 2));
        session.progress.style.width = Math.round(((index + 1) / poses.length) * 100) + '%';
        await new Promise((resolve) => setTimeout(resolve, 650));
      }

      return {
        descriptors,
        label: descriptors.length + ' foto wajah tersimpan'
      };
    } finally {
      closeCameraOverlay(session);
    }
  }

  window.PadiFaceApi = { captureDescriptor, captureEnrollment };
})();

import React, { useRef, useEffect } from 'react';
import * as THREE from 'three';
import { Radio, AlertTriangle } from 'lucide-react';

export const ThreeGeoRadar: React.FC = () => {
  const mountRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const mount = mountRef.current;
    if (!mount) return;

    const width = mount.clientWidth;
    const height = mount.clientHeight;

    // Scene & Camera
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 100);
    camera.position.set(0, 7, 9);
    camera.lookAt(0, 0, 0);

    // Renderer
    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    mount.appendChild(renderer.domElement);

    // 1. Concentric Sonar Rings
    const ringGroup = new THREE.Group();
    scene.add(ringGroup);

    const radii = [1.2, 2.4, 3.6, 4.8];
    const ringMeshes: THREE.LineLoop[] = [];

    radii.forEach((r) => {
      const ringGeo = new THREE.BufferGeometry();
      const points = [];
      for (let theta = 0; theta <= Math.PI * 2; theta += Math.PI / 32) {
        points.push(new THREE.Vector3(Math.cos(theta) * r, 0, Math.sin(theta) * r));
      }
      ringGeo.setFromPoints(points);
      const ringMat = new THREE.LineBasicMaterial({ color: 0x10b981, transparent: true, opacity: 0.35 });
      const ring = new THREE.LineLoop(ringGeo, ringMat);
      ringMeshes.push(ring);
      ringGroup.add(ring);
    });

    // Crosshairs
    const lineMat = new THREE.LineBasicMaterial({ color: 0x10b981, transparent: true, opacity: 0.2 });
    const xGeo = new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(-5, 0, 0), new THREE.Vector3(5, 0, 0)]);
    const zGeo = new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(0, 0, -5), new THREE.Vector3(0, 0, 5)]);
    scene.add(new THREE.Line(xGeo, lineMat));
    scene.add(new THREE.Line(zGeo, lineMat));

    // 2. Center User Farm Node (Glowing green cylinder beacon)
    const userBeaconGeo = new THREE.CylinderGeometry(0.15, 0.15, 1.2, 16);
    const userBeaconMat = new THREE.MeshBasicMaterial({ color: 0xf5c842, transparent: true, opacity: 0.9 });
    const userBeacon = new THREE.Mesh(userBeaconGeo, userBeaconMat);
    userBeacon.position.y = 0.6;
    scene.add(userBeacon);

    // 3. Outbreak Danger Beacons (Red & Amber 3D Pillars)
    const threatBeacons: THREE.Mesh[] = [];

    const addThreat = (x: number, z: number, color: number) => {
      const threatGeo = new THREE.CylinderGeometry(0.1, 0.2, 1.6, 16);
      const threatMat = new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 0.85 });
      const threat = new THREE.Mesh(threatGeo, threatMat);
      threat.position.set(x, 0.8, z);
      scene.add(threat);
      threatBeacons.push(threat);

      // Light beam halo
      const haloGeo = new THREE.RingGeometry(0.2, 0.45, 16);
      const haloMat = new THREE.MeshBasicMaterial({ color, side: THREE.DoubleSide, transparent: true, opacity: 0.6 });
      const halo = new THREE.Mesh(haloGeo, haloMat);
      halo.rotation.x = Math.PI / 2;
      halo.position.set(x, 0.05, z);
      scene.add(halo);
    };

    addThreat(2.2, -1.8, 0xef4444); // Blast 3.2km
    addThreat(-2.6, 2.1, 0xef4444); // Blast 5.4km
    addThreat(-1.5, -2.4, 0xf59e0b); // HDB 2.1km

    // 4. Rotating Sonar Wave Beam
    const radarArmGeo = new THREE.PlaneGeometry(5, 0.08);
    const radarArmMat = new THREE.MeshBasicMaterial({ color: 0x34d399, transparent: true, opacity: 0.75, side: THREE.DoubleSide });
    const radarArm = new THREE.Mesh(radarArmGeo, radarArmMat);
    radarArm.rotation.x = Math.PI / 2;
    radarArm.position.x = 2.5;
    const radarSweepGroup = new THREE.Group();
    radarSweepGroup.add(radarArm);
    scene.add(radarSweepGroup);

    // Interactive Drag
    let isDragging = false;
    let prevX = 0;

    const onMouseDown = (e: MouseEvent) => {
      isDragging = true;
      prevX = e.clientX;
    };
    const onMouseMove = (e: MouseEvent) => {
      if (!isDragging) return;
      const deltaX = e.clientX - prevX;
      scene.rotation.y += deltaX * 0.008;
      prevX = e.clientX;
    };
    const onMouseUp = () => {
      isDragging = false;
    };

    mount.addEventListener('mousedown', onMouseDown);
    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', onMouseUp);

    // Animation Loop
    let clock = new THREE.Clock();
    let animId: number;

    const animate = () => {
      animId = requestAnimationFrame(animate);
      const time = clock.getElapsedTime();

      // Sweep rotation
      radarSweepGroup.rotation.y = -time * 1.5;

      // Pulse beacon heights
      threatBeacons.forEach((b, idx) => {
        b.scale.y = 1 + Math.sin(time * 3 + idx) * 0.25;
      });

      // Gentle auto-rotation if not dragging
      if (!isDragging) {
        scene.rotation.y = time * 0.08;
      }

      renderer.render(scene, camera);
    };

    animate();

    const onResize = () => {
      if (!mount) return;
      const w = mount.clientWidth;
      const h = mount.clientHeight;
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      renderer.setSize(w, h);
    };
    window.addEventListener('resize', onResize);

    return () => {
      cancelAnimationFrame(animId);
      mount.removeEventListener('mousedown', onMouseDown);
      window.removeEventListener('mousemove', onMouseMove);
      window.removeEventListener('mouseup', onMouseUp);
      window.removeEventListener('resize', onResize);
      if (mount.contains(renderer.domElement)) {
        mount.removeChild(renderer.domElement);
      }
      renderer.dispose();
    };
  }, []);

  return (
    <div className="relative w-full h-[400px] sm:h-[460px] rounded-3xl bg-gradient-to-b from-[#091C12] to-[#040D08] border border-[#10B981]/30 overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.6)] flex items-center justify-center">
      {/* 3D Canvas */}
      <div ref={mountRef} className="w-full h-full cursor-grab active:cursor-grabbing" />

      {/* Top HUD */}
      <div className="absolute top-4 left-4 right-4 flex items-center justify-between pointer-events-none">
        <div className="flex items-center gap-2 px-3 py-1 rounded-full bg-black/70 backdrop-blur-md border border-white/10 text-xs text-white">
          <Radio className="w-3.5 h-3.5 text-red-400 animate-pulse" />
          <span className="font-mono text-[11px]">3D_RADAR_SONAR::RADIUS_8KM</span>
        </div>
        <div className="text-[10px] text-white/50 font-mono">
          [ Drag untuk putar radar ]
        </div>
      </div>

      {/* Bottom Danger Ticker */}
      <div className="absolute bottom-4 left-4 right-4 bg-[#081710]/90 backdrop-blur-xl p-3.5 rounded-2xl border border-white/10 flex items-center justify-between text-xs font-mono text-white">
        <div className="flex items-center gap-2">
          <span className="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping" />
          <span>Blast 3.2km (2 Kasus)</span>
        </div>
        <div className="flex items-center gap-2">
          <span className="w-2.5 h-2.5 rounded-full bg-amber-400" />
          <span>HDB 2.1km (1 Kasus)</span>
        </div>
      </div>
    </div>
  );
};

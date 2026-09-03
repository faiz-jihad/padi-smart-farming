import React, { useRef, useEffect, useState } from 'react';
import * as THREE from 'three';
import { Scan, Sparkles, ShieldCheck, Info } from 'lucide-react';

export const ThreePlantScanner: React.FC = () => {
  const mountRef = useRef<HTMLDivElement>(null);
  const [activeZone, setActiveZone] = useState<'healthy' | 'infected'>('infected');

  useEffect(() => {
    const mount = mountRef.current;
    if (!mount) return;

    const width = mount.clientWidth;
    const height = mount.clientHeight;

    // Scene & Camera
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 100);
    camera.position.set(0, 0, 9.5);

    // WebGL Renderer
    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    mount.appendChild(renderer.domElement);

    // 1. Procedural 3D Rice Leaf Geometry
    const shape = new THREE.Shape();
    shape.moveTo(0, -3.8);
    shape.quadraticCurveTo(1.4, -1.5, 1.3, 1.2);
    shape.quadraticCurveTo(0.7, 3.2, 0, 4.2);
    shape.quadraticCurveTo(-0.7, 3.2, -1.3, 1.2);
    shape.quadraticCurveTo(-1.4, -1.5, 0, -3.8);

    const extrudeSettings = {
      depth: 0.15,
      bevelEnabled: true,
      bevelSegments: 3,
      steps: 2,
      bevelSize: 0.08,
      bevelThickness: 0.08,
    };

    const geometry = new THREE.ExtrudeGeometry(shape, extrudeSettings);

    // Surface Material with Cellular Organic Texture
    const leafMaterial = new THREE.MeshStandardMaterial({
      color: 0x1e6e3c,
      roughness: 0.35,
      metalness: 0.15,
      wireframe: false,
    });

    const leafMesh = new THREE.Mesh(geometry, leafMaterial);
    leafMesh.rotation.z = -0.15;
    scene.add(leafMesh);

    // 2. Wireframe Overlay for High-Tech AI Computer Vision Mesh
    const wireframeMat = new THREE.MeshBasicMaterial({
      color: 0x34d399,
      wireframe: true,
      transparent: true,
      opacity: 0.25,
    });
    const wireframeMesh = new THREE.Mesh(geometry, wireframeMat);
    wireframeMesh.position.z = 0.02;
    leafMesh.add(wireframeMesh);

    // 3. Central Leaf Midrib Vein Line
    const veinPoints = [
      new THREE.Vector3(0, -3.8, 0.1),
      new THREE.Vector3(0.05, -1, 0.12),
      new THREE.Vector3(-0.02, 1.5, 0.14),
      new THREE.Vector3(0, 4.2, 0.1),
    ];
    const veinCurve = new THREE.CatmullRomCurve3(veinPoints);
    const veinGeo = new THREE.TubeGeometry(veinCurve, 20, 0.06, 6, false);
    const veinMat = new THREE.MeshBasicMaterial({ color: 0x4ade80 });
    const veinMesh = new THREE.Mesh(veinGeo, veinMat);
    leafMesh.add(veinMesh);

    // 4. Infected Lesion 3D Node (Hawar Daun Bakteri Zone)
    const hotspotGeo = new THREE.SphereGeometry(0.35, 16, 16);
    const hotspotMat = new THREE.MeshBasicMaterial({
      color: 0xf59e0b,
      transparent: true,
      opacity: 0.85,
      wireframe: true,
    });
    const hotspot = new THREE.Mesh(hotspotGeo, hotspotMat);
    hotspot.position.set(0.6, 1.2, 0.2);
    leafMesh.add(hotspot);

    // 5. 3D Laser Scan Plane
    const scanPlaneGeo = new THREE.PlaneGeometry(3.5, 0.08);
    const scanPlaneMat = new THREE.MeshBasicMaterial({
      color: 0x34d399,
      side: THREE.DoubleSide,
      transparent: true,
      opacity: 0.9,
    });
    const scanPlane = new THREE.Mesh(scanPlaneGeo, scanPlaneMat);
    scene.add(scanPlane);

    // 6. Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
    scene.add(ambientLight);

    const dirLight = new THREE.DirectionalLight(0xf5c842, 2.2);
    dirLight.position.set(5, 8, 6);
    scene.add(dirLight);

    const rimLight = new THREE.DirectionalLight(0x34d399, 1.5);
    rimLight.position.set(-5, -4, -4);
    scene.add(rimLight);

    // 7. Interactive Drag / Hover Rotation
    let isDragging = false;
    let prevMouseX = 0;
    let prevMouseY = 0;

    const onMouseDown = (e: MouseEvent) => {
      isDragging = true;
      prevMouseX = e.clientX;
      prevMouseY = e.clientY;
    };

    const onMouseMove = (e: MouseEvent) => {
      if (!isDragging) return;
      const deltaX = e.clientX - prevMouseX;
      const deltaY = e.clientY - prevMouseY;

      leafMesh.rotation.y += deltaX * 0.008;
      leafMesh.rotation.x += deltaY * 0.008;

      prevMouseX = e.clientX;
      prevMouseY = e.clientY;
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

      // Gentle floating sway if not dragging
      if (!isDragging) {
        leafMesh.rotation.y = Math.sin(time * 0.8) * 0.25;
        leafMesh.rotation.z = -0.15 + Math.cos(time * 0.6) * 0.05;
      }

      // Sweeping Laser Animation
      scanPlane.position.y = Math.sin(time * 1.5) * 3.2;

      // Pulse lesion hotspot
      const scale = 1 + Math.sin(time * 4) * 0.2;
      hotspot.scale.set(scale, scale, scale);

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
      geometry.dispose();
      leafMaterial.dispose();
      wireframeGeoDispose: wireframeMat.dispose();
      renderer.dispose();
    };
  }, []);

  return (
    <div className="relative w-full h-[450px] sm:h-[500px] rounded-3xl bg-gradient-to-b from-[#0B1E15] to-[#06120C] border border-[#10B981]/30 overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.6)] flex items-center justify-center">
      {/* 3D WebGL Canvas */}
      <div
        ref={mountRef}
        className="w-full h-full cursor-grab active:cursor-grabbing"
      />

      {/* Top Hologram Status Overlay */}
      <div className="absolute top-4 left-4 right-4 flex items-center justify-between pointer-events-none">
        <div className="flex items-center gap-2 px-3 py-1 rounded-full bg-black/60 backdrop-blur-md border border-white/10 text-xs text-white">
          <Scan className="w-3.5 h-3.5 text-[#34D399] animate-pulse" />
          <span className="font-mono text-[11px]">3D_SCANNER_YOLO11::ACTIVE</span>
        </div>
        <div className="text-[10px] text-white/50 font-mono">
          [ Drag untuk putar 3D ]
        </div>
      </div>

      {/* Bottom Real-Time Diagnostic Node */}
      <div className="absolute bottom-4 left-4 right-4 bg-[#081710]/95 backdrop-blur-xl p-4 rounded-2xl border border-white/10 text-left space-y-2.5 pointer-events-auto shadow-2xl">
        <div className="flex items-start justify-between">
          <div>
            <div className="text-[10px] text-[#34D399] font-mono uppercase tracking-wider font-bold">
              PATOGEN TERDETEKSI
            </div>
            <h4 className="text-base font-extrabold text-white leading-tight">
              Hawar Daun Bakteri
            </h4>
            <p className="text-[11px] text-white/50 italic">
              Xanthomonas oryzae pv. oryzae
            </p>
          </div>
          <div className="text-right">
            <span className="text-lg font-black text-[#F5C842]">94.7%</span>
            <div className="text-[9px] text-white/50">Keyakinan AI</div>
          </div>
        </div>

        {/* Severity Metrics */}
        <div className="grid grid-cols-2 gap-2 text-xs border-t border-white/10 pt-2 font-mono">
          <div className="bg-black/30 p-2 rounded-xl flex items-center justify-between">
            <span className="text-white/60">Tingkat Risiko:</span>
            <span className="font-bold text-amber-400">Sedang</span>
          </div>
          <div className="bg-black/30 p-2 rounded-xl flex items-center justify-between">
            <span className="text-white/60">Area Lesi:</span>
            <span className="font-bold text-[#34D399]">12.4%</span>
          </div>
        </div>
      </div>
    </div>
  );
};

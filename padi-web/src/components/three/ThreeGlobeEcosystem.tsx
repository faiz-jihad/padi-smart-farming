import React, { useRef, useEffect } from 'react';
import * as THREE from 'three';

export const ThreeGlobeEcosystem: React.FC = () => {
  const mountRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const mount = mountRef.current;
    if (!mount) return;

    const width = mount.clientWidth;
    const height = mount.clientHeight;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 100);
    camera.position.set(0, 0, 8.5);

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    mount.appendChild(renderer.domElement);

    // 1. Center Glowing Core Sphere
    const coreGeo = new THREE.SphereGeometry(1.6, 32, 32);
    const coreMat = new THREE.MeshStandardMaterial({
      color: 0x075b3b,
      roughness: 0.3,
      metalness: 0.7,
      emissive: 0x063d28,
      emissiveIntensity: 0.6,
      wireframe: false,
    });
    const core = new THREE.Mesh(coreGeo, coreMat);
    scene.add(core);

    // Core Wireframe Grid
    const coreWireMat = new THREE.MeshBasicMaterial({ color: 0x34d399, wireframe: true, transparent: true, opacity: 0.25 });
    const coreWire = new THREE.Mesh(coreGeo, coreWireMat);
    scene.add(coreWire);

    // 2. Orbital Rings
    const orbitGroup = new THREE.Group();
    scene.add(orbitGroup);

    const createRing = (radius: number, rotX: number, rotY: number, color: number) => {
      const ringGeo = new THREE.TorusGeometry(radius, 0.02, 16, 100);
      const ringMat = new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 0.6 });
      const ring = new THREE.Mesh(ringGeo, ringMat);
      ring.rotation.x = rotX;
      ring.rotation.y = rotY;
      orbitGroup.add(ring);
      return ring;
    };

    createRing(2.7, Math.PI / 3, 0, 0xf5c842);
    createRing(3.2, -Math.PI / 4, Math.PI / 6, 0x10b981);
    createRing(3.8, Math.PI / 6, -Math.PI / 4, 0x38bdf8);

    // 3. Orbiting Satellite Intelligence Nodes
    const nodes = [
      { name: 'AI Scan', color: 0x10b981, dist: 2.7, angle: 0 },
      { name: 'Cuaca', color: 0x38bdf8, dist: 3.2, angle: Math.PI * 0.4 },
      { name: 'Radar', color: 0xef4444, dist: 3.8, angle: Math.PI * 0.8 },
      { name: 'PPL', color: 0xf5c842, dist: 2.7, angle: Math.PI * 1.2 },
      { name: 'HST', color: 0x34d399, dist: 3.2, angle: Math.PI * 1.6 },
    ];

    const satelliteMeshes: THREE.Mesh[] = [];
    nodes.forEach((n) => {
      const satGeo = new THREE.SphereGeometry(0.2, 16, 16);
      const satMat = new THREE.MeshBasicMaterial({ color: n.color });
      const sat = new THREE.Mesh(satGeo, satMat);
      scene.add(sat);
      satelliteMeshes.push(sat);
    });

    // 4. Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 1);
    scene.add(ambientLight);
    const dirLight = new THREE.DirectionalLight(0xf5c842, 2);
    dirLight.position.set(5, 5, 5);
    scene.add(dirLight);

    // Mouse Interaction
    let isDragging = false;
    let prevX = 0;
    let prevY = 0;

    const onMouseDown = (e: MouseEvent) => {
      isDragging = true;
      prevX = e.clientX;
      prevY = e.clientY;
    };
    const onMouseMove = (e: MouseEvent) => {
      if (!isDragging) return;
      const deltaX = e.clientX - prevX;
      const deltaY = e.clientY - prevY;
      scene.rotation.y += deltaX * 0.008;
      scene.rotation.x += deltaY * 0.008;
      prevX = e.clientX;
      prevY = e.clientY;
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

      // Rotate core & orbits
      if (!isDragging) {
        core.rotation.y = time * 0.3;
        coreWire.rotation.y = time * 0.3;
        orbitGroup.rotation.y = time * 0.15;
        orbitGroup.rotation.z = Math.sin(time * 0.2) * 0.1;
      }

      // Animate satellites along orbits
      nodes.forEach((n, idx) => {
        const theta = n.angle + time * 0.4;
        const x = Math.cos(theta) * n.dist;
        const z = Math.sin(theta) * n.dist;
        const y = Math.sin(theta * 2) * 0.6;
        satelliteMeshes[idx].position.set(x, y, z);
      });

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
      coreGeo.dispose();
      coreMat.dispose();
      renderer.dispose();
    };
  }, []);

  return (
    <div className="relative w-full h-[440px] sm:h-[500px] rounded-3xl bg-gradient-to-b from-[#0B1E15] to-[#040E08] border border-[#10B981]/30 overflow-hidden shadow-2xl flex items-center justify-center">
      {/* 3D Canvas */}
      <div ref={mountRef} className="w-full h-full cursor-grab active:cursor-grabbing" />

      {/* Center Hologram Text Overlay */}
      <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
        <div className="w-24 h-24 rounded-full bg-black/40 backdrop-blur-md border border-[#F5C842]/40 flex flex-col items-center justify-center shadow-lg">
          <span className="text-[9px] font-mono uppercase text-[#F5C842]">KERNEL</span>
          <span className="text-xl font-black text-white">P.A.D.I.</span>
        </div>
      </div>

      <div className="absolute bottom-4 left-4 right-4 text-center pointer-events-none">
        <span className="text-xs text-white/50 font-mono">
          [ Drag untuk memutar 3D ekosistem ]
        </span>
      </div>
    </div>
  );
};

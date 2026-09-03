import React, { useRef, useEffect } from 'react';
import * as THREE from 'three';

export const ThreeHeroScene: React.FC = () => {
  const mountRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const mount = mountRef.current;
    if (!mount) return;

    // Respect reduced motion
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const width = mount.clientWidth || window.innerWidth;
    const height = mount.clientHeight || window.innerHeight;

    // 1. Scene & Camera
    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x061a10, 0.035);

    const camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 1000);
    camera.position.set(0, 12, 28);
    camera.lookAt(0, 0, 0);

    // 2. WebGL Renderer
    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true, powerPreference: 'high-performance' });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);
    mount.appendChild(renderer.domElement);

    // 3. Wavy Rice Field Particle Canopy
    const cols = 75;
    const rows = 75;
    const count = cols * rows;
    const positions = new Float32Array(count * 3);
    const colors = new Float32Array(count * 3);

    const colorA = new THREE.Color(0x0c7047); // Emerald rice green
    const colorB = new THREE.Color(0xf5c842); // Harvest gold
    const colorC = new THREE.Color(0x41a55b); // Fresh leaf

    let i = 0;
    for (let x = 0; x < cols; x++) {
      for (let z = 0; z < rows; z++) {
        // Grid distribution
        const u = (x - cols / 2) * 0.9;
        const v = (z - rows / 2) * 0.9;

        positions[i] = u;
        positions[i + 1] = Math.sin(u * 0.3) * Math.cos(v * 0.3) * 2;
        positions[i + 2] = v;

        // Color blend based on depth and position
        const mixedColor = new THREE.Color();
        const factor = (x / cols + z / rows) * 0.5;
        if (factor > 0.6) {
          mixedColor.lerpColors(colorC, colorB, (factor - 0.6) * 2.5);
        } else {
          mixedColor.lerpColors(colorA, colorC, factor / 0.6);
        }

        colors[i] = mixedColor.r;
        colors[i + 1] = mixedColor.g;
        colors[i + 2] = mixedColor.b;

        i += 3;
      }
    }

    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    // Particle texture
    const canvas = document.createElement('canvas');
    canvas.width = 16;
    canvas.height = 16;
    const ctx = canvas.getContext('2d');
    if (ctx) {
      const grad = ctx.createRadialGradient(8, 8, 0, 8, 8, 8);
      grad.addColorStop(0, 'rgba(255,255,255,1)');
      grad.addColorStop(0.4, 'rgba(255,255,255,0.7)');
      grad.addColorStop(1, 'rgba(255,255,255,0)');
      ctx.fillStyle = grad;
      ctx.fillRect(0, 0, 16, 16);
    }
    const texture = new THREE.CanvasTexture(canvas);

    const material = new THREE.PointsMaterial({
      size: 0.65,
      map: texture,
      vertexColors: true,
      transparent: true,
      opacity: 0.85,
      blending: THREE.AdditiveBlending,
      depthWrite: false,
    });

    const particles = new THREE.Points(geometry, material);
    particles.position.y = -4;
    scene.add(particles);

    // 4. Floating Atmospheric Spores / Light Motes
    const sporeCount = 120;
    const sporeGeo = new THREE.BufferGeometry();
    const sporePos = new Float32Array(sporeCount * 3);
    for (let s = 0; s < sporeCount * 3; s += 3) {
      sporePos[s] = (Math.random() - 0.5) * 45;
      sporePos[s + 1] = Math.random() * 20 - 2;
      sporePos[s + 2] = (Math.random() - 0.5) * 45;
    }
    sporeGeo.setAttribute('position', new THREE.BufferAttribute(sporePos, 3));

    const sporeMat = new THREE.PointsMaterial({
      size: 0.9,
      map: texture,
      color: 0xf5c842,
      transparent: true,
      opacity: 0.7,
      blending: THREE.AdditiveBlending,
      depthWrite: false,
    });
    const spores = new THREE.Points(sporeGeo, sporeMat);
    scene.add(spores);

    // 5. Ambient Sun Beam Line Grid
    const gridHelper = new THREE.GridHelper(60, 30, 0x1a4530, 0x0c2a1c);
    gridHelper.position.y = -6.5;
    scene.add(gridHelper);

    // 6. Interactive Mouse Ripple
    let mouseX = 0;
    let mouseY = 0;
    let targetX = 0;
    let targetY = 0;

    const onMouseMove = (e: MouseEvent) => {
      const windowHalfX = window.innerWidth / 2;
      const windowHalfY = window.innerHeight / 2;
      mouseX = (e.clientX - windowHalfX) * 0.02;
      mouseY = (e.clientY - windowHalfY) * 0.02;
    };
    window.addEventListener('mousemove', onMouseMove, { passive: true });

    // 7. Animation Loop
    let clock = new THREE.Clock();
    let animId: number;

    const animate = () => {
      animId = requestAnimationFrame(animate);

      const time = clock.getElapsedTime();
      const pos = geometry.attributes.position.array as Float32Array;

      // Smooth camera sway following mouse
      targetX += (mouseX - targetX) * 0.05;
      targetY += (mouseY - targetY) * 0.05;
      camera.position.x = targetX * 0.5;
      camera.position.y = 12 - targetY * 0.3;
      camera.lookAt(0, 0, 0);

      // Wavy terrain motion
      if (!prefersReducedMotion) {
        let index = 0;
        for (let x = 0; x < cols; x++) {
          for (let z = 0; z < rows; z++) {
            const u = pos[index];
            const w = pos[index + 2];
            // Multi-harmonic wave simulating wind flowing across rice fields
            pos[index + 1] =
              Math.sin(u * 0.25 + time * 1.5) * 1.2 +
              Math.cos(w * 0.25 + time * 1.2) * 1.2 +
              Math.sin((u + w) * 0.15 + time * 0.8) * 0.6;
            index += 3;
          }
        }
        geometry.attributes.position.needsUpdate = true;

        // Floating spores drift
        spores.rotation.y = time * 0.04;
        const sPos = sporeGeo.attributes.position.array as Float32Array;
        for (let s = 1; s < sporeCount * 3; s += 3) {
          sPos[s] += Math.sin(time + s) * 0.015;
        }
        sporeGeo.attributes.position.needsUpdate = true;
      }

      renderer.render(scene, camera);
    };

    animate();

    // 8. Resize Handler
    const onResize = () => {
      if (!mount) return;
      const newW = mount.clientWidth || window.innerWidth;
      const newH = mount.clientHeight || window.innerHeight;
      camera.aspect = newW / newH;
      camera.updateProjectionMatrix();
      renderer.setSize(newW, newH);
    };
    window.addEventListener('resize', onResize);

    // Cleanup
    return () => {
      cancelAnimationFrame(animId);
      window.removeEventListener('mousemove', onMouseMove);
      window.removeEventListener('resize', onResize);
      if (mount.contains(renderer.domElement)) {
        mount.removeChild(renderer.domElement);
      }
      geometry.dispose();
      material.dispose();
      sporeGeo.dispose();
      sporeMat.dispose();
      renderer.dispose();
    };
  }, []);

  return (
    <div
      ref={mountRef}
      className="absolute inset-0 w-full h-full pointer-events-none overflow-hidden"
      aria-hidden="true"
    />
  );
};

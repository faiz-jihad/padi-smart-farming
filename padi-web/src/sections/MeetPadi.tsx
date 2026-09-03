import React, { useRef, useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { PhoneMockup } from '../components/padi/PhoneMockup';
import { FarmPriority } from '../components/padi/FarmPriority';
import { Sparkles, Layers } from 'lucide-react';

gsap.registerPlugin(ScrollTrigger);

export const MeetPadi: React.FC = () => {
  const sectionRef = useRef<HTMLDivElement>(null);
  const phoneWrapperRef = useRef<HTMLDivElement>(null);
  const textRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const ctx = gsap.context(() => {
      const tl = gsap.timeline({
        scrollTrigger: {
          trigger: sectionRef.current,
          start: 'top 75%',
          end: 'center center',
          scrub: 1,
        },
      });

      // Phone enters from bottom center with tilt reduction and scale
      tl.fromTo(
        phoneWrapperRef.current,
        {
          y: 120,
          scale: 0.88,
          rotateX: 6,
          opacity: 0.4,
        },
        {
          y: 0,
          scale: 1,
          rotateX: 0,
          opacity: 1,
          ease: 'power2.out',
        }
      );

      // Subtle text lift
      tl.fromTo(
        textRef.current,
        { y: 30, opacity: 0 },
        { y: 0, opacity: 1, ease: 'power2.out' },
        0
      );
    }, sectionRef);

    return () => ctx.revert();
  }, []);

  return (
    <section
      id="tentang"
      ref={sectionRef}
      className="relative w-full min-h-screen bg-[#F5F2E9] text-[#063D2B] py-24 px-4 sm:px-6 flex flex-col items-center justify-center text-center overflow-hidden"
    >
      {/* Subtle Background Contour Lines */}
      <div className="absolute inset-0 opacity-10 bg-[radial-gradient(#075B3B_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none" />

      {/* Narrative Intro Headers */}
      <div ref={textRef} className="max-w-3xl mx-auto mb-10 z-10 space-y-3">
        <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#0C7047]/10 text-[#0C7047] text-xs font-black uppercase tracking-wider">
          <Layers className="w-3.5 h-3.5" />
          <span>Sistem Operasi Cerdas Pertanian Padi</span>
        </div>

        <h2 className="text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-[#063D2B] leading-none">
          Inilah P.A.D.I.
        </h2>

        <p className="text-xl sm:text-2xl md:text-3xl font-extrabold text-[#0C7047] max-w-2xl mx-auto leading-tight">
          Satu intelligence layer untuk seluruh aktivitas sawah.
        </p>

        <p className="text-sm sm:text-base text-[#141A17]/70 max-w-lg mx-auto font-medium leading-relaxed">
          Mengintegrasikan diagnosa citra AI, telemetri cuaca mikro, kalender musim tanam, dan validasi penyuluh langsung ke telapak tangan petani.
        </p>
      </div>

      {/* Realistic Smartphone Showcase */}
      <div
        ref={phoneWrapperRef}
        className="relative z-20 will-change-transform perspective-1000 mt-2"
      >
        <PhoneMockup>
          <FarmPriority />
        </PhoneMockup>
      </div>
    </section>
  );
};

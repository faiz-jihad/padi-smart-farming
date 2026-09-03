import React, { useRef, useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Download, ArrowDown } from 'lucide-react';

gsap.registerPlugin(ScrollTrigger);

export const Opening: React.FC = () => {
  const sectionRef = useRef<HTMLDivElement>(null);
  const bgRef = useRef<HTMLDivElement>(null);
  const contentRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const ctx = gsap.context(() => {
      // Cinematic slow background parallax like Scout Motors
      gsap.to(bgRef.current, {
        yPercent: 18,
        scale: 1.06,
        ease: 'none',
        scrollTrigger: {
          trigger: sectionRef.current,
          start: 'top top',
          end: 'bottom top',
          scrub: true,
        },
      });

      // Subtle upward fade of headline
      gsap.to(contentRef.current, {
        yPercent: -15,
        opacity: 0.25,
        ease: 'none',
        scrollTrigger: {
          trigger: sectionRef.current,
          start: 'top top',
          end: 'bottom top',
          scrub: true,
        },
      });
    }, sectionRef);

    return () => ctx.revert();
  }, []);

  return (
    <section
      ref={sectionRef}
      className="relative w-full min-h-screen flex flex-col justify-between px-6 sm:px-12 md:px-20 pt-32 sm:pt-40 pb-12 overflow-hidden bg-[#0A140F] select-none text-[#F5F2EB]"
    >
      {/* Cinematic Full-Bleed Rice Field Background */}
      <div
        ref={bgRef}
        className="absolute inset-0 bg-cover bg-center will-change-transform opacity-45 scale-100"
        style={{ backgroundImage: `url('/images/hero_paddy.jpg')` }}
        aria-hidden="true"
      >
        {/* Soft atmospheric gradient framing */}
        <div className="absolute inset-0 bg-gradient-to-t from-[#0A140F] via-[#0A140F]/40 to-[#0A140F]/60" />
      </div>

      <div className="relative z-10 w-full" />

      {/* Main Editorial Content */}
      <div
        ref={contentRef}
        className="relative z-10 max-w-4xl text-left my-auto space-y-6 sm:space-y-8 will-change-transform"
      >
        <div className="space-y-3">
          <div className="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#C9A96E]">
            Teknologi untuk Tanah dan Padi
          </div>

          <h1 className="text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-black tracking-tight leading-[1.05] text-white">
            Dibuat untuk mereka yang turun ke pematang saat fajar.
          </h1>
        </div>

        <p className="text-base sm:text-lg md:text-xl text-[#F5F2EB]/80 max-w-2xl leading-relaxed font-normal">
          Alat baru untuk tradisi yang telah menghidupi negeri berabad-abad. Membantu petani membaca tanda-tanda pada tanaman, memahami pergerakan cuaca, dan mengambil keputusan yang tepat untuk setiap petak sawah.
        </p>

        {/* Scout-Style Tactile Buttons */}
        <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
          <a
            href="#masalah"
            className="inline-flex items-center justify-center px-8 py-4 rounded-full text-xs font-bold tracking-wider uppercase text-[#0A140F] bg-[#F5F2EB] hover:bg-white active:scale-95 transition-all shadow-md"
          >
            Lihat Bagaimana Cara Kerjanya
          </a>

          <a
            href="/downloads/padi-latest.apk"
            download="PADI-latest.apk"
            className="inline-flex items-center justify-center gap-2 px-7 py-4 rounded-full text-xs font-bold tracking-wider uppercase text-white bg-white/10 hover:bg-white/15 border border-white/20 active:scale-95 transition-all"
          >
            <Download className="w-4 h-4 text-[#C9A96E]" />
            <span>Unduh untuk Android</span>
          </a>
        </div>
      </div>

      {/* Understated Bottom Scroll Indicator */}
      <div className="relative z-10 text-left text-xs text-white/40 font-medium flex items-center gap-2">
        <span>Gulir perlahan untuk melihat kisah P.A.D.I.</span>
        <ArrowDown className="w-3 h-3 text-[#C9A96E] animate-bounce" />
      </div>
    </section>
  );
};

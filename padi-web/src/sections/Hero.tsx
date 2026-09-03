import React, { useRef, useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Download, ChevronDown, Sparkles, ArrowRight } from 'lucide-react';

gsap.registerPlugin(ScrollTrigger);

export const Hero: React.FC = () => {
  const sectionRef = useRef<HTMLDivElement>(null);
  const bgRef = useRef<HTMLDivElement>(null);
  const sunRef = useRef<HTMLDivElement>(null);
  const headlineRef = useRef<HTMLDivElement>(null);
  const foregroundStalksRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const ctx = gsap.context(() => {
      // Scrub Parallax Animation for Cinematic Hero
      const tl = gsap.timeline({
        scrollTrigger: {
          trigger: sectionRef.current,
          start: 'top top',
          end: 'bottom top',
          scrub: true,
        },
      });

      // Background scales gently down from 1.08 to 1.0
      tl.to(bgRef.current, { scale: 1, ease: 'none' }, 0);

      // Sunlight expansion
      tl.to(sunRef.current, { scale: 1.4, opacity: 0.8, ease: 'none' }, 0);

      // Headline moves subtly upward and fades
      tl.to(headlineRef.current, { y: -80, opacity: 0.3, ease: 'none' }, 0);

      // Foreground stalks move faster to create cinematic depth
      tl.to(foregroundStalksRef.current, { yPercent: -25, ease: 'none' }, 0);
    }, sectionRef);

    return () => ctx.revert();
  }, []);

  return (
    <section
      ref={sectionRef}
      className="relative w-full min-h-screen flex flex-col justify-between items-center text-center px-4 pt-28 pb-10 overflow-hidden select-none bg-[#06291C]"
    >
      {/* 1. Cinematic Background Layer with Sunrise Horizon */}
      <div
        ref={bgRef}
        className="absolute inset-0 bg-gradient-to-b from-[#0B4F35] via-[#073D29] to-[#041A12] scale-108 will-change-transform"
        aria-hidden="true"
      >
        {/* Atmospheric Morning Mist / Fog Gradients */}
        <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-100/10 via-transparent to-black/40" />

        {/* Rice Field Horizon Contour SVGs */}
        <svg
          viewBox="0 0 1440 400"
          className="absolute bottom-0 w-full h-auto opacity-30 text-[#072418] pointer-events-none"
          preserveAspectRatio="none"
        >
          <path
            fill="currentColor"
            d="M0,192L48,197.3C96,203,192,213,288,202.7C384,192,480,160,576,165.3C672,171,768,213,864,224C960,235,1056,213,1152,192C1248,171,1344,149,1392,138.7L1440,128L1440,400L1392,400C1344,400,1248,400,1152,400C1056,400,960,400,864,400C768,400,672,400,576,400C480,400,384,400,288,400C192,400,96,400,48,400L0,400Z"
          />
        </svg>
      </div>

      {/* 2. Expanding Atmospheric Sunlight */}
      <div
        ref={sunRef}
        className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[320px] sm:w-[500px] h-[320px] sm:h-[500px] rounded-full sunlight-glow opacity-50 blur-3xl pointer-events-none"
        aria-hidden="true"
      />

      {/* 3. Hero Content Container */}
      <div
        ref={headlineRef}
        className="relative z-10 max-w-4xl mx-auto flex flex-col items-center justify-center my-auto will-change-transform"
      >
        {/* Subtle Badge */}
        <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-[#F5C842] text-xs font-extrabold mb-5 shadow-sm">
          <Sparkles className="w-3.5 h-3.5" />
          <span>Predictive Agriculture & Disease Intelligence</span>
        </div>

        {/* Big Brand Title */}
        <h1 className="text-6xl sm:text-7xl md:text-8xl lg:text-9xl font-black text-white tracking-tighter leading-none mb-3 drop-shadow-lg">
          P.A.D.I.
        </h1>

        {/* Editorial Subtitle */}
        <div className="text-lg sm:text-2xl md:text-3xl font-bold text-[#F5C842] tracking-tight mb-4">
          Lebih dari sekadar aplikasi pertanian.
        </div>

        {/* Core Narrative Statement */}
        <p className="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-snug max-w-2xl">
          Intelligence untuk setiap keputusan di sawah.
        </p>

        {/* Supporting Copy */}
        <p className="text-sm sm:text-base md:text-lg text-white/75 max-w-xl mx-auto mt-4 font-normal leading-relaxed">
          Pantau kondisi sawah, deteksi penyakit, pahami cuaca mikro, dan dapatkan rekomendasi tindakan presisi dalam satu aplikasi.
        </p>

        {/* Action Buttons */}
        <div className="flex flex-col sm:flex-row items-center gap-3.5 mt-8 w-full sm:w-auto px-4">
          <a
            href="#masalah"
            className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-2xl bg-[#0C7047] hover:bg-[#075B3B] text-white font-extrabold text-sm border border-[#41A55B]/40 shadow-lg transition-all"
          >
            <span>Jelajahi P.A.D.I.</span>
            <ArrowRight className="w-4 h-4" />
          </a>

          <a
            href="/downloads/padi-latest.apk"
            download="PADI-latest.apk"
            className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-2xl bg-[#F5C842] hover:bg-[#ebd056] text-[#063D2B] font-black text-sm shadow-xl transition-all"
          >
            <Download className="w-4 h-4 stroke-[2.5]" />
            <span>Download APK (Android)</span>
          </a>
        </div>
      </div>

      {/* 4. Foreground Rice Stalks Parallax Layer */}
      <div
        ref={foregroundStalksRef}
        className="absolute bottom-0 inset-x-0 h-32 md:h-44 pointer-events-none z-20 flex justify-between items-end opacity-40 will-change-transform"
        aria-hidden="true"
      >
        <svg viewBox="0 0 100 80" className="w-28 md:w-44 text-[#062418] fill-current">
          <path d="M10 80 C 15 50, 25 30, 40 10 C 35 35, 25 60, 20 80 Z" />
          <path d="M25 80 C 35 45, 50 25, 70 5 C 60 30, 45 60, 35 80 Z" />
        </svg>
        <svg viewBox="0 0 100 80" className="w-32 md:w-52 text-[#062418] fill-current transform scale-x-[-1]">
          <path d="M10 80 C 15 50, 25 30, 40 10 C 35 35, 25 60, 20 80 Z" />
          <path d="M25 80 C 35 45, 50 25, 70 5 C 60 30, 45 60, 35 80 Z" />
        </svg>
      </div>

      {/* 5. Scroll Prompt Indicator */}
      <div className="relative z-10 flex flex-col items-center gap-1.5 text-white/50 text-[11px] font-semibold animate-pulse">
        <span>Scroll untuk melihat bagaimana P.A.D.I. bekerja</span>
        <ChevronDown className="w-4 h-4" />
      </div>
    </section>
  );
};

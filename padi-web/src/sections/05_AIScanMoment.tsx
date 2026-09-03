import React, { useRef, useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export const AIScanMoment: React.FC = () => {
  const sectionRef = useRef<HTMLDivElement>(null);
  const resultRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const ctx = gsap.context(() => {
      gsap.fromTo(
        resultRef.current,
        { opacity: 0, y: 15 },
        {
          opacity: 1,
          y: 0,
          duration: 0.8,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: sectionRef.current,
            start: 'top 60%',
            toggleActions: 'play none none reverse',
          },
        }
      );
    }, sectionRef);

    return () => ctx.revert();
  }, []);

  return (
    <section
      ref={sectionRef}
      className="relative w-full bg-[#08120C] text-white py-28 sm:py-36 px-6 sm:px-12 flex flex-col items-center justify-center text-center overflow-hidden border-t border-white/5"
    >
      <div className="max-w-2xl mx-auto space-y-4 mb-12">
        <div className="text-xs font-bold uppercase tracking-widest text-[#C9A96E]">
          Pemindaian Gejala Daun
        </div>

        <h2 className="text-3xl sm:text-5xl font-black tracking-tight text-white leading-tight">
          Satu pemindaian yang membumi.
        </h2>

        <p className="text-sm sm:text-base text-white/70 max-w-md mx-auto leading-relaxed">
          Cukup arahkan kamera pada helai daun yang dicurigai. P.A.D.I. membaca pola bercak untuk memberikan dugaan awal penyakit padi.
        </p>
      </div>

      {/* Grounded, Realistic Camera Canvas with Rice Leaf */}
      <div className="relative w-full max-w-sm h-88 sm:h-96 rounded-3xl overflow-hidden bg-[#0D1C13] border border-white/10 shadow-2xl flex items-center justify-center">
        {/* Real Rice Leaf Photograph */}
        <img
          src="/images/onboarding_1.jpeg"
          alt="Helai daun padi terindikasi hawar daun"
          className="w-full h-full object-cover opacity-90 filter contrast-105"
        />

        {/* Clean, Simple Rectangular Frame Guide (No sci-fi neon) */}
        <div className="absolute inset-8 border border-white/35 rounded-xl pointer-events-none">
          <div className="absolute -top-1 -left-1 w-3.5 h-3.5 border-t-2 border-l-2 border-white" />
          <div className="absolute -top-1 -right-1 w-3.5 h-3.5 border-t-2 border-r-2 border-white" />
          <div className="absolute -bottom-1 -left-1 w-3.5 h-3.5 border-b-2 border-l-2 border-white" />
          <div className="absolute -bottom-1 -right-1 w-3.5 h-3.5 border-b-2 border-r-2 border-white" />
        </div>

        {/* Single Gentle Scanning Line */}
        <div className="absolute inset-x-8 h-[1.5px] bg-white/85 animate-natural-scan pointer-events-none" />

        {/* Grounded Diagnosis Card */}
        <div
          ref={resultRef}
          className="absolute bottom-4 inset-x-4 bg-[#0A1710]/95 backdrop-blur-md p-4 rounded-2xl border border-white/15 text-left space-y-2 shadow-xl"
        >
          <div className="flex items-start justify-between">
            <div>
              <h4 className="text-sm font-bold text-white">Hawar Daun Bakteri</h4>
              <p className="text-[11px] text-white/50 italic">Xanthomonas oryzae pv. oryzae</p>
            </div>
            <span className="text-xs font-bold text-[#C9A96E]">94.7%</span>
          </div>

          <div className="flex items-center justify-between text-[10.5px] text-white/60 border-t border-white/10 pt-2">
            <span>Tingkat Risiko: <strong className="text-amber-400 font-semibold">Sedang</strong></span>
            <span className="text-white/40">Prediksi awal AI</span>
          </div>
        </div>
      </div>

      {/* Honest Caption */}
      <div className="max-w-sm mx-auto mt-6 text-xs text-white/50 leading-relaxed text-center">
        Jika diperlukan, foto daun ini dapat diteruskan langsung ke petugas penyuluh pertanian lapangan untuk konfirmasi resep penanganan.
      </div>
    </section>
  );
};

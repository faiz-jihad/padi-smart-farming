import React, { useRef, useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Sun, CloudRain, Clock } from 'lucide-react';

gsap.registerPlugin(ScrollTrigger);

export const WeatherSection: React.FC = () => {
  const sectionRef = useRef<HTMLDivElement>(null);
  const skyRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const ctx = gsap.context(() => {
      // Subtle cloud parallax
      gsap.to(skyRef.current, {
        yPercent: 15,
        ease: 'none',
        scrollTrigger: {
          trigger: sectionRef.current,
          start: 'top bottom',
          end: 'bottom top',
          scrub: true,
        },
      });
    }, sectionRef);

    return () => ctx.revert();
  }, []);

  return (
    <section
      id="cuaca"
      ref={sectionRef}
      className="relative w-full min-h-screen bg-[#081811] text-white flex flex-col justify-between overflow-hidden border-t border-white/5"
    >
      {/* Sky & Field Background Layer taking half of viewport */}
      <div
        ref={skyRef}
        className="absolute inset-x-0 top-0 h-[60%] bg-cover bg-center opacity-30 will-change-transform"
        style={{ backgroundImage: `url('/images/splash_background.jpeg')` }}
        aria-hidden="true"
      >
        <div className="absolute inset-0 bg-gradient-to-b from-[#081811]/40 via-transparent to-[#081811]" />
      </div>

      <div className="relative z-10 pt-24 px-6 text-center max-w-xl mx-auto space-y-3">
        <span className="text-xs font-semibold uppercase tracking-widest text-[#D4A017]">
          Agro-Meteorologi
        </span>
        <h2 className="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight">
          Hari ini sebaiknya melakukan apa?
        </h2>
        <p className="text-sm text-white/60">
          Cuaca bukan sekadar angka suhu, melainkan batasan waktu untuk tindakan di pematang.
        </p>
      </div>

      {/* Actionable Decision Card Overlay Below */}
      <div className="relative z-10 pb-20 px-6 max-w-md mx-auto w-full">
        <div className="bg-[#122018]/95 backdrop-blur-md p-5 rounded-3xl border border-white/10 text-left space-y-4 shadow-xl">
          <div className="flex items-center justify-between border-b border-white/5 pb-3">
            <div>
              <span className="text-2xl font-bold text-white">28°C</span>
              <span className="text-xs text-white/50 block">Indramayu &bull; Stasiun Terdekat</span>
            </div>
            <Sun className="w-7 h-7 text-[#D4A017]" />
          </div>

          <div className="space-y-2.5 text-xs">
            <div className="bg-black/20 p-3 rounded-2xl flex items-start gap-3">
              <Clock className="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" />
              <div>
                <span className="text-emerald-400 font-bold block text-xs">Pemupukan Disarankan</span>
                <span className="text-sm font-bold text-white block mt-0.5">09.00 – 11.00 WIB</span>
                <span className="text-[11px] text-white/50">Waktu aman penyerapan sebelum panas terik.</span>
              </div>
            </div>

            <div className="bg-black/20 p-3 rounded-2xl flex items-start gap-3">
              <CloudRain className="w-4 h-4 text-amber-400 shrink-0 mt-0.5" />
              <div>
                <span className="text-amber-300 font-bold block text-xs">Hujan Diperkirakan</span>
                <span className="text-sm font-bold text-white block mt-0.5">Setelah 14.00 WIB</span>
                <span className="text-[11px] text-white/50">Hindari penyemprotan siang agar obat tidak hanyut.</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

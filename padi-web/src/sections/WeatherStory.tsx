import React, { useRef, useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { WeatherCard } from '../components/padi/WeatherCard';
import { CloudSun, Wind, Droplets } from 'lucide-react';

gsap.registerPlugin(ScrollTrigger);

export const WeatherStory: React.FC = () => {
  const sectionRef = useRef<HTMLDivElement>(null);
  const cloud1Ref = useRef<HTMLDivElement>(null);
  const cloud2Ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const ctx = gsap.context(() => {
      // Parallax cloud movement at different scroll speeds
      gsap.to(cloud1Ref.current, {
        xPercent: 15,
        ease: 'none',
        scrollTrigger: {
          trigger: sectionRef.current,
          start: 'top bottom',
          end: 'bottom top',
          scrub: true,
        },
      });

      gsap.to(cloud2Ref.current, {
        xPercent: -20,
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
      ref={sectionRef}
      className="relative w-full bg-gradient-to-b from-[#061F15] via-[#0B3A28] to-[#0A1F16] text-white py-28 px-4 sm:px-6 flex items-center justify-center overflow-hidden"
    >
      {/* Parallax Floating Cloud SVGs */}
      <div
        ref={cloud1Ref}
        className="absolute top-10 -left-20 w-96 h-36 bg-white/5 rounded-full blur-3xl pointer-events-none will-change-transform"
        aria-hidden="true"
      />
      <div
        ref={cloud2Ref}
        className="absolute bottom-16 -right-20 w-[450px] h-44 bg-emerald-400/5 rounded-full blur-3xl pointer-events-none will-change-transform"
        aria-hidden="true"
      />

      <div className="max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center text-left relative z-10">
        {/* Left: Narrative Message */}
        <div className="lg:col-span-6 space-y-4">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#075B3B] text-[#F5C842] text-xs font-bold border border-[#41A55B]/30">
            <CloudSun className="w-3.5 h-3.5" />
            <span>Agro-Meteorologi Presisi</span>
          </div>

          <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.15]">
            Cuaca bukan <br />
            <span className="text-[#38BDF8]">sekadar angka.</span>
          </h2>

          <p className="text-xl sm:text-2xl font-extrabold text-[#F5C842] leading-snug">
            P.A.D.I. menerjemahkan cuaca menjadi keputusan lapangan.
          </p>

          <p className="text-sm sm:text-base text-white/80 leading-relaxed max-w-lg">
            Suhu 28°C atau angin 12 km/j tidak banyak membantu jika tidak dikaitkan dengan fisiologi tanaman padi. P.A.D.I. secara otomatis menghitung kapan stomata daun membuka dan kapan saat teraman untuk memupuk tanpa risiko terbilas hujan.
          </p>
        </div>

        {/* Right: Weather Telemetry Card */}
        <div className="lg:col-span-6 flex justify-center">
          <div className="w-full max-w-sm">
            <WeatherCard />
          </div>
        </div>
      </div>
    </section>
  );
};

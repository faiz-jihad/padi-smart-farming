import React, { useRef, useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Sprout } from 'lucide-react';

gsap.registerPlugin(ScrollTrigger);

export const ImpactStory: React.FC = () => {
  const containerRef = useRef<HTMLDivElement>(null);
  const line1Ref = useRef<HTMLDivElement>(null);
  const line2Ref = useRef<HTMLDivElement>(null);
  const line3Ref = useRef<HTMLDivElement>(null);
  const line4Ref = useRef<HTMLDivElement>(null);
  const finalRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const ctx = gsap.context(() => {
      const tl = gsap.timeline({
        scrollTrigger: {
          trigger: containerRef.current,
          start: 'top 70%',
          end: 'bottom 40%',
          scrub: 0.8,
        },
      });

      tl.fromTo(line1Ref.current, { opacity: 0.2, y: 30 }, { opacity: 1, y: 0, ease: 'none' })
        .fromTo(line2Ref.current, { opacity: 0.2, y: 30 }, { opacity: 1, y: 0, ease: 'none' }, '+=0.2')
        .fromTo(line3Ref.current, { opacity: 0.2, y: 30 }, { opacity: 1, y: 0, ease: 'none' }, '+=0.2')
        .fromTo(line4Ref.current, { opacity: 0.2, y: 30 }, { opacity: 1, y: 0, ease: 'none' }, '+=0.2')
        .fromTo(finalRef.current, { opacity: 0, scale: 0.95 }, { opacity: 1, scale: 1, ease: 'none' }, '+=0.3');
    }, containerRef);

    return () => ctx.revert();
  }, []);

  return (
    <section
      ref={containerRef}
      className="relative w-full min-h-screen bg-[#04140D] text-white py-32 px-4 sm:px-6 flex flex-col items-center justify-center text-center overflow-hidden border-t border-white/5"
    >
      <div className="max-w-4xl mx-auto space-y-8 sm:space-y-12">
        <div
          ref={line1Ref}
          className="text-4xl sm:text-6xl md:text-7xl font-black text-white/90 tracking-tighter"
        >
          Lebih cepat mendeteksi.
        </div>

        <div
          ref={line2Ref}
          className="text-4xl sm:text-6xl md:text-7xl font-black text-[#41A55B] tracking-tighter"
        >
          Lebih tepat bertindak.
        </div>

        <div
          ref={line3Ref}
          className="text-4xl sm:text-6xl md:text-7xl font-black text-[#F5C842] tracking-tighter"
        >
          Lebih siap menghadapi risiko.
        </div>

        <div
          ref={line4Ref}
          className="text-4xl sm:text-6xl md:text-7xl font-black text-white tracking-tighter leading-tight"
        >
          Lebih percaya diri mengambil keputusan.
        </div>

        {/* Final Brand Resolution */}
        <div ref={finalRef} className="pt-16 space-y-4 border-t border-white/10 max-w-xl mx-auto">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#0C7047] text-white text-xs font-bold">
            <Sprout className="w-3.5 h-3.5 text-[#F5C842]" />
            <span>P.A.D.I.</span>
          </div>

          <p className="text-xl sm:text-2xl font-extrabold text-white/90 leading-snug">
            Teknologi yang membantu petani memahami sawahnya.
          </p>

          <p className="text-xs sm:text-sm text-white/60">
            Didedikasikan untuk kedaulatan pangan, ketahanan panen, dan kesejahteraan petani Nusantara.
          </p>
        </div>
      </div>
    </section>
  );
};

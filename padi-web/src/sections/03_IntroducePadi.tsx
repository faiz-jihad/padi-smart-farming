import React, { useRef, useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { RealPhoneFrame } from '../components/phone/RealPhoneFrame';
import { HomeScreen } from '../components/phone/screens/HomeScreen';

gsap.registerPlugin(ScrollTrigger);

export const IntroducePadi: React.FC = () => {
  const sectionRef = useRef<HTMLDivElement>(null);
  const phoneRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const ctx = gsap.context(() => {
      gsap.fromTo(
        phoneRef.current,
        { y: 100, opacity: 0.6 },
        {
          y: 0,
          opacity: 1,
          ease: 'power3.out',
          scrollTrigger: {
            trigger: sectionRef.current,
            start: 'top 75%',
            end: 'center center',
            scrub: 0.6,
          },
        }
      );
    }, sectionRef);

    return () => ctx.revert();
  }, []);

  return (
    <section
      ref={sectionRef}
      className="relative w-full bg-[#F7F5F0] text-[#141A16] py-28 px-6 sm:px-12 flex flex-col items-center justify-center text-center overflow-hidden border-t border-[#E5DFD3]"
    >
      <div className="max-w-2xl mx-auto space-y-4 mb-12">
        <h2 className="text-3xl sm:text-4xl md:text-5xl font-extrabold text-[#0C3825] tracking-tight leading-tight">
          P.A.D.I. membantu menjawab pertanyaan itu.
        </h2>

        <p className="text-sm sm:text-base md:text-lg text-[#141A16]/75 max-w-lg mx-auto leading-relaxed">
          Satu aplikasi untuk melihat kondisi sawah, mendeteksi penyakit, memahami cuaca, dan menentukan tindakan berikutnya.
        </p>
      </div>

      {/* Real Phone Mockup Center Stage */}
      <div ref={phoneRef} className="will-change-transform">
        <RealPhoneFrame>
          <HomeScreen />
        </RealPhoneFrame>
      </div>
    </section>
  );
};

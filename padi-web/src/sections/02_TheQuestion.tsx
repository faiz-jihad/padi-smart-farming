import React, { useRef, useEffect, useState } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export const TheQuestion: React.FC = () => {
  const containerRef = useRef<HTMLDivElement>(null);
  const stickyRef = useRef<HTMLDivElement>(null);
  const textGroupRef = useRef<HTMLDivElement>(null);
  const imgRef = useRef<HTMLImageElement>(null);
  const [activeStep, setActiveStep] = useState(0);

  const dilemmas = [
    {
      title: 'Di sawah, keputusan kecil bisa menentukan hasil.',
      context: 'Setiap musim tanam, seorang petani mengambil ratusan keputusan tanpa kepastian penuh.',
    },
    {
      title: '“Daun ini sakit atau sekadar layu sesaat?”',
      context: 'Melihat bercak kekuningan di pucuk daun, menebak apakah ini awal serangan hawar atau sekadar stres air sementara.',
    },
    {
      title: '“Apakah aman memupuk hari ini?”',
      context: 'Salah memperhitungkan tanda langit berarti pupuk mahal hanyut terbilas hujan lebat dua jam setelah disebar.',
    },
    {
      title: '“Harus melakukan apa setelah penyakit ditemukan?”',
      context: 'Mengetahui nama penyakit saja belum cukup tanpa kepastian tindakan yang harus diambil sebelum masalah meluas ke petak tetangga.',
    },
  ];

  useEffect(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      setActiveStep(dilemmas.length - 1);
      return;
    }

    const container = containerRef.current;
    const sticky = stickyRef.current;
    if (!container || !sticky) return;

    let lastStep = -1;

    const ctx = gsap.context(() => {
      // 1. Pin Section & Scrub through 4 dilemmas
      ScrollTrigger.create({
        trigger: container,
        start: 'top top',
        end: 'bottom bottom',
        pin: sticky,
        pinSpacing: false,
        scrub: 0.5,
        onUpdate: (self) => {
          const step = Math.min(
            dilemmas.length - 1,
            Math.floor(self.progress * dilemmas.length)
          );

          if (step !== lastStep) {
            lastStep = step;
            setActiveStep(step);

            // Smooth text transition on step switch
            if (textGroupRef.current) {
              gsap.fromTo(
                textGroupRef.current,
                { y: 18, opacity: 0.2 },
                { y: 0, opacity: 1, duration: 0.45, ease: 'power2.out' }
              );
            }
          }
        },
      });

      // 2. Slow subtle zoom of the documentary rice leaf photo across the scroll
      if (imgRef.current) {
        gsap.to(imgRef.current, {
          scale: 1.12,
          ease: 'none',
          scrollTrigger: {
            trigger: container,
            start: 'top top',
            end: 'bottom bottom',
            scrub: true,
          },
        });
      }
    }, containerRef);

    return () => ctx.revert();
  }, [dilemmas.length]);

  const current = dilemmas[activeStep] || dilemmas[0];

  return (
    <div
      id="masalah"
      ref={containerRef}
      className="relative w-full h-[340vh] bg-[#F5F2EB] text-[#14231A] select-none"
    >
      <div
        ref={stickyRef}
        className="sticky top-0 left-0 w-full h-screen flex items-center justify-center px-6 sm:px-12 md:px-20 py-12 overflow-hidden"
      >
        <div className="max-w-6xl mx-auto w-full grid grid-cols-1 md:grid-cols-12 gap-10 sm:gap-16 items-center">
          {/* Left: Magazine / Editorial Sticky Typography with ScrollTrigger Reactivity */}
          <div className="md:col-span-7 text-left space-y-6">
            <div className="text-xs font-bold uppercase tracking-widest text-[#0C3825]/60">
              Pertanyaan di Pematang Sawah
            </div>

            <div ref={textGroupRef} className="space-y-4">
              <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-[#0C3825] tracking-tight leading-[1.15]">
                {current.title}
              </h2>

              <p className="text-base sm:text-lg text-[#14231A]/70 max-w-lg leading-relaxed">
                {current.context}
              </p>
            </div>

            {/* Minimal step indicator */}
            <div className="flex items-center gap-2 pt-4">
              {dilemmas.map((_, idx) => (
                <div
                  key={idx}
                  className={`h-1 rounded-full transition-all duration-300 ${
                    idx === activeStep
                      ? 'w-12 bg-[#0C3825]'
                      : 'w-2.5 bg-[#0C3825]/20'
                  }`}
                />
              ))}
            </div>
          </div>

          {/* Right: Close-up Documentary Photography of Rice Leaf with Scroll Parallax */}
          <div className="md:col-span-5 flex justify-center">
            <div className="relative w-72 sm:w-80 h-96 sm:h-[440px] rounded-2xl overflow-hidden shadow-xl border border-[#E5DFD3]">
              <img
                ref={imgRef}
                src="/images/onboarding_1.jpeg"
                alt="Pengamatan daun padi"
                className="w-full h-full object-cover will-change-transform scale-100"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent" />
              <div className="absolute bottom-4 left-4 right-4 text-xs text-white/80 font-medium">
                Pemeriksaan helai daun &bull; Indramayu
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

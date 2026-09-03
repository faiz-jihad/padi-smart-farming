import React, { useRef, useEffect, useState } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { HelpCircle, AlertTriangle } from 'lucide-react';

gsap.registerPlugin(ScrollTrigger);

export const FarmingProblem: React.FC = () => {
  const containerRef = useRef<HTMLDivElement>(null);
  const stickyRef = useRef<HTMLDivElement>(null);
  const [activeStep, setActiveStep] = useState(0);

  const dilemmas = [
    {
      label: 'Realitas di Lapangan',
      text: 'Setiap hari petani harus mengambil keputusan.',
      sub: 'Di tengah ketidakpastian cuaca dan ancaman hama yang tak kasat mata.',
      lesionOpacity: 0,
    },
    {
      label: 'Pertanyaan #1',
      text: '“Apakah tanaman saya sehat?”',
      sub: 'Melihat warna daun di pagi hari, menebak kecukupan nutrisi tanah.',
      lesionOpacity: 0.15,
    },
    {
      label: 'Pertanyaan #2',
      text: '“Apakah daun ini terkena penyakit?”',
      sub: 'Bercak kuning di ujung daun: apakah hawar bakteri, blast, atau sekadar terbakar terik?',
      lesionOpacity: 0.45,
    },
    {
      label: 'Pertanyaan #3',
      text: '“Apakah hari ini aman untuk pemupukan?”',
      sub: 'Jika hujan turun siang nanti, ratusan ribu rupiah pupuk akan terbuang sia-sia.',
      lesionOpacity: 0.65,
    },
    {
      label: 'Pertanyaan #4',
      text: '“Apakah ada penyakit yang menyebar di sekitar sawah?”',
      sub: 'Kabar angin serangan wereng di desa sebelah tanpa kepastian jarak dan waktu mitigasi.',
      lesionOpacity: 0.85,
    },
    {
      label: 'Risiko Terbesar',
      text: 'Dan keputusan yang terlambat berarti kehilangan hasil panen.',
      sub: 'Keterlambatan 3 hari menangani hawar daun dapat memotong hingga 40% hasil gabah.',
      lesionOpacity: 1,
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

    const ctx = gsap.context(() => {
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
          setActiveStep(step);
        },
      });
    }, containerRef);

    return () => ctx.revert();
  }, [dilemmas.length]);

  const currentDilemma = dilemmas[activeStep] || dilemmas[0];

  return (
    <div
      id="masalah"
      ref={containerRef}
      className="relative w-full h-[400vh] bg-[#0E1712] text-white select-none"
    >
      <div
        ref={stickyRef}
        className="sticky top-0 left-0 w-full h-screen flex flex-col justify-center px-4 sm:px-6 lg:px-12 py-10 overflow-hidden"
      >
        {/* Background Subtle Radial Amber Glow on Higher Alert */}
        <div
          className="absolute -right-20 top-1/2 -translate-y-1/2 w-96 h-96 rounded-full bg-amber-500/10 blur-3xl transition-opacity duration-700 pointer-events-none"
          style={{ opacity: currentDilemma.lesionOpacity }}
        />

        <div className="max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
          {/* Left Column: Editorial Dilemma Storytelling */}
          <div className="lg:col-span-7 text-left space-y-4">
            {/* Progress Step Indicator */}
            <div className="flex items-center gap-2">
              <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-[#F5C842] border border-white/15">
                <HelpCircle className="w-3.5 h-3.5" />
                <span>{currentDilemma.label}</span>
              </span>
              <span className="text-xs text-white/40 font-semibold">
                {activeStep + 1} / {dilemmas.length}
              </span>
            </div>

            {/* Main Dilemma Question Statement */}
            <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.15] transition-all duration-300">
              {currentDilemma.text}
            </h2>

            {/* Contextual Elaboration */}
            <p className="text-sm sm:text-base md:text-lg text-white/70 max-w-lg leading-relaxed transition-all duration-300">
              {currentDilemma.sub}
            </p>

            {/* Step Progress Dots */}
            <div className="flex items-center gap-2 pt-4">
              {dilemmas.map((_, idx) => (
                <div
                  key={idx}
                  className={`h-1.5 rounded-full transition-all duration-300 ${
                    idx === activeStep
                      ? 'w-8 bg-[#F5C842]'
                      : idx < activeStep
                      ? 'w-2 bg-[#41A55B]'
                      : 'w-2 bg-white/20'
                  }`}
                />
              ))}
            </div>
          </div>

          {/* Right Column: Close-up Rice Leaf with Progressive Symptoms */}
          <div className="lg:col-span-5 flex items-center justify-center">
            <div className="relative w-72 sm:w-80 h-96 sm:h-[420px] rounded-3xl bg-gradient-to-b from-[#0A261A] to-[#05170F] border border-white/10 p-6 flex flex-col items-center justify-center shadow-2xl overflow-hidden">
              <div className="absolute top-4 left-4 text-[10px] text-white/40 uppercase tracking-widest font-mono">
                Pemeriksaan Morfologi Daun
              </div>

              {/* Stylized Rice Leaf Close-up Illustration */}
              <svg viewBox="0 0 240 320" className="w-56 h-72 drop-shadow-xl">
                {/* Main Rice Leaf Body */}
                <path
                  d="M120 20 C 180 80, 200 200, 140 300 C 80 220, 60 100, 120 20 Z"
                  fill="#1B5E34"
                  stroke="#2E8B57"
                  strokeWidth="2"
                />

                {/* Leaf Central Midrib */}
                <path d="M120 25 Q 140 160 135 295" stroke="#4ADE80" strokeWidth="2.5" fill="none" opacity="0.6" />

                {/* Secondary Veinlets */}
                <path d="M125 70 Q 155 100 170 120" stroke="#4ADE80" strokeWidth="1" fill="none" opacity="0.4" />
                <path d="M115 110 Q 85 140 75 170" stroke="#4ADE80" strokeWidth="1" fill="none" opacity="0.4" />
                <path d="M130 160 Q 165 190 175 220" stroke="#4ADE80" strokeWidth="1" fill="none" opacity="0.4" />

                {/* Progressive Necrotic Lesion (Symptom development driven by scroll) */}
                <g style={{ opacity: currentDilemma.lesionOpacity }} className="transition-opacity duration-500">
                  {/* Outer Chlorotic Yellow Halo */}
                  <path
                    d="M120 25 C 145 60, 160 110, 145 130 C 130 110, 115 50, 120 25 Z"
                    fill="#F5C842"
                    opacity="0.8"
                  />
                  {/* Inner Necrotic Brown Lesion Center */}
                  <path
                    d="M120 30 C 135 55, 145 90, 138 105 C 130 90, 118 50, 120 30 Z"
                    fill="#8B4513"
                    opacity="0.9"
                  />
                  {/* Bacterial Leaf Blight Wavy Edge */}
                  <path
                    d="M120 30 Q 140 70 148 115"
                    stroke="#D2691E"
                    strokeWidth="2.5"
                    fill="none"
                  />
                </g>
              </svg>

              {/* Symptom Badge Alert */}
              <div
                className="mt-2 text-center text-xs transition-opacity duration-300"
                style={{ opacity: currentDilemma.lesionOpacity > 0.2 ? 1 : 0 }}
              >
                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40 text-[11px] font-bold">
                  <AlertTriangle className="w-3.5 h-3.5" />
                  <span>Gejala Awal Hawar Daun Terdeteksi</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

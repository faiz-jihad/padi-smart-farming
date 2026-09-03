import React, { useRef, useEffect, useState } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { RealPhoneFrame } from '../components/phone/RealPhoneFrame';
import { HomeScreen } from '../components/phone/screens/HomeScreen';
import { ScanScreen } from '../components/phone/screens/ScanScreen';
import { ResultScreen } from '../components/phone/screens/ResultScreen';
import { ActionScreen } from '../components/phone/screens/ActionScreen';
import { WeatherScreen } from '../components/phone/screens/WeatherScreen';
import { CropCycleScreen } from '../components/phone/screens/CropCycleScreen';
import { RadarScreen } from '../components/phone/screens/RadarScreen';

gsap.registerPlugin(ScrollTrigger);

export const AppWalkthrough: React.FC = () => {
  const containerRef = useRef<HTMLDivElement>(null);
  const stickyRef = useRef<HTMLDivElement>(null);
  const textColRef = useRef<HTMLDivElement>(null);
  const screenWrapperRef = useRef<HTMLDivElement>(null);
  const [activeStep, setActiveStep] = useState(0);

  const steps = [
    {
      screen: 'home',
      label: 'BERANDA',
      title: 'Yang penting hari ini, langsung terlihat.',
      sub: 'Prioritas tindakan disajikan sederhana tanpa membuat bingung pengguna.',
    },
    {
      screen: 'scan',
      label: 'FOTO DAUN',
      title: 'Arahkan kamera. P.A.D.I. membantu membaca polanya.',
      sub: 'Mengenali bercak dan lesi pada helai daun padi langsung di pematang.',
    },
    {
      screen: 'result',
      label: 'DIAGNOSA',
      title: 'Prediksi penyakit disertai tingkat keyakinan terbuka.',
      sub: 'Hasil analisis disajikan jujur sebagai panduan awal petani.',
    },
    {
      screen: 'action',
      label: 'TINDAKAN',
      title: 'Bukan hanya tahu masalahnya. Tahu apa yang harus dilakukan.',
      sub: 'Rekomendasi penanganan praktis untuk hari ini dan opsi ke penyuluh.',
    },
    {
      screen: 'weather',
      label: 'CUACA SAWAH',
      title: 'Cuaca diterjemahkan menjadi keputusan nyata.',
      sub: 'Menentukan jam terbaik pemupukan sebelum hujan sore turun.',
    },
    {
      screen: 'crop',
      label: 'UMUR TANAMAN',
      title: 'Setiap aktivitas mengikuti fase pertumbuhan.',
      sub: 'Melacak hari setelah tanam agar dosis pupuk tepat sasaran.',
    },
    {
      screen: 'radar',
      label: 'RADAR HAMPARAN',
      title: 'Ketahui risiko penyakit di sekitar sawah Anda.',
      sub: 'Peringatan dini berdasarkan laporan gotong royong petani sekitar.',
    },
  ];

  useEffect(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      setActiveStep(0);
      return;
    }

    const container = containerRef.current;
    const sticky = stickyRef.current;
    if (!container || !sticky) return;

    let prevStep = -1;

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
            steps.length - 1,
            Math.floor(self.progress * steps.length)
          );

          if (step !== prevStep) {
            prevStep = step;
            setActiveStep(step);

            // Animate text transition with ScrollTrigger
            if (textColRef.current) {
              gsap.fromTo(
                textColRef.current,
                { y: 16, opacity: 0.3 },
                { y: 0, opacity: 1, duration: 0.4, ease: 'power2.out' }
              );
            }

            // Animate phone screen transition
            if (screenWrapperRef.current) {
              gsap.fromTo(
                screenWrapperRef.current,
                { opacity: 0.5, scale: 0.98 },
                { opacity: 1, scale: 1, duration: 0.35, ease: 'power2.out' }
              );
            }
          }
        },
      });
    }, containerRef);

    return () => ctx.revert();
  }, [steps.length]);

  const current = steps[activeStep] || steps[0];

  const renderActiveScreen = () => {
    switch (current.screen) {
      case 'home':
        return <HomeScreen />;
      case 'scan':
        return <ScanScreen />;
      case 'result':
        return <ResultScreen />;
      case 'action':
        return <ActionScreen />;
      case 'weather':
        return <WeatherScreen />;
      case 'crop':
        return <CropCycleScreen />;
      case 'radar':
        return <RadarScreen />;
      default:
        return <HomeScreen />;
    }
  };

  return (
    <div
      id="fitur"
      ref={containerRef}
      className="relative w-full h-[520vh] bg-[#0A140F] text-[#F5F2EB] select-none border-t border-white/5"
    >
      <div
        ref={stickyRef}
        className="sticky top-0 left-0 w-full h-screen flex items-center justify-center px-6 sm:px-12 md:px-20 py-10 overflow-hidden"
      >
        <div className="max-w-6xl mx-auto w-full grid grid-cols-1 md:grid-cols-12 gap-8 md:gap-16 items-center">
          {/* Left: Animated Text Triggered by Scroll */}
          <div className="md:col-span-6 text-left space-y-5">
            <div className="text-xs font-bold uppercase tracking-widest text-[#C9A96E]">
              {current.label} &bull; 0{activeStep + 1} / 07
            </div>

            <div ref={textColRef} className="space-y-3">
              <h3 className="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight leading-tight">
                {current.title}
              </h3>

              <p className="text-sm sm:text-base md:text-lg text-white/70 max-w-md leading-relaxed">
                {current.sub}
              </p>
            </div>

            {/* Minimal step progress line */}
            <div className="flex items-center gap-1.5 pt-4">
              {steps.map((_, idx) => (
                <div
                  key={idx}
                  className={`h-1 rounded-full transition-all duration-300 ${
                    idx === activeStep
                      ? 'w-10 bg-[#C9A96E]'
                      : 'w-2 bg-white/20'
                  }`}
                />
              ))}
            </div>
          </div>

          {/* Right: Pinned Real Smartphone Screen */}
          <div className="md:col-span-6 flex justify-center">
            <RealPhoneFrame>
              <div ref={screenWrapperRef} className="w-full h-full">
                {renderActiveScreen()}
              </div>
            </RealPhoneFrame>
          </div>
        </div>
      </div>
    </div>
  );
};

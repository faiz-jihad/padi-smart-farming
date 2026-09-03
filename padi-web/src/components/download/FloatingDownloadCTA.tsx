import React, { useEffect, useRef, useState } from 'react';
import { Download, Smartphone } from 'lucide-react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export const FloatingDownloadCTA: React.FC = () => {
  const ctaRef = useRef<HTMLDivElement>(null);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      const scrollY = window.scrollY;
      const docHeight = document.documentElement.scrollHeight;
      const winHeight = window.innerHeight;

      // Show after 80vh (scroll > 0.8 * window.innerHeight)
      // Hide when near the bottom (final download section, ~ winHeight from bottom)
      const shouldShow = scrollY > winHeight * 0.75 && scrollY < docHeight - winHeight * 1.5;
      setVisible(shouldShow);
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <div
      ref={ctaRef}
      className={`fixed bottom-6 left-1/2 -translate-x-1/2 z-50 transition-all duration-500 transform ${
        visible
          ? 'translate-y-0 opacity-100 scale-100 pointer-events-auto'
          : 'translate-y-16 opacity-0 scale-90 pointer-events-none'
      }`}
    >
      <a
        href="/downloads/padi-latest.apk"
        download="PADI-latest.apk"
        className="flex items-center gap-2.5 px-5 py-3 rounded-full bg-[#F5C842] text-[#063D2B] font-extrabold text-xs sm:text-sm shadow-[0_10px_25px_rgba(245,200,66,0.35)] hover:bg-[#ffe270] active:scale-95 transition-all border border-[#F5C842]/40 backdrop-blur-md"
      >
        <Smartphone className="w-4 h-4" />
        <span>Download APK Android</span>
        <Download className="w-3.5 h-3.5 ml-0.5 stroke-[2.5]" />
      </a>
    </div>
  );
};

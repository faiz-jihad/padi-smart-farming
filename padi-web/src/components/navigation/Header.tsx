import React, { useState, useEffect } from 'react';
import { Download } from 'lucide-react';

export const Header: React.FC = () => {
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const onScroll = () => {
      setScrolled(window.scrollY > 40);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  return (
    <header
      className={`fixed top-0 left-0 w-full z-50 transition-all duration-500 select-none ${
        scrolled
          ? 'bg-[#0A140F]/90 backdrop-blur-md border-b border-white/10 py-4'
          : 'bg-transparent py-6 sm:py-8'
      }`}
    >
      <div className="max-w-6xl mx-auto px-6 sm:px-12 flex items-center justify-between">
        {/* Left: Understated Brand Mark */}
        <a href="#" className="flex items-center gap-3">
          <span className="text-xl sm:text-2xl font-extrabold tracking-tight text-white">
            P.A.D.I.
          </span>
          <span className="hidden sm:inline-block w-1 h-1 rounded-full bg-[#C9A96E]" />
          <span className="hidden sm:inline-block text-xs text-white/60 font-medium">
            Kecerdasan Agrikultur
          </span>
        </a>

        {/* Center / Navigation Links */}
        <nav className="hidden md:flex items-center gap-8 text-xs font-medium text-white/70">
          <a href="#masalah" className="hover:text-white transition-colors">
            Tentang
          </a>
          <a href="#fitur" className="hover:text-white transition-colors">
            Aplikasi
          </a>
          <a href="#cerita" className="hover:text-white transition-colors">
            Kisah Lapangan
          </a>
          <a href="#cuaca" className="hover:text-white transition-colors">
            Cuaca
          </a>
        </nav>

        {/* Right: Tactile Download Button */}
        <div>
          <a
            href="/downloads/padi-latest.apk"
            download="PADI-latest.apk"
            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold text-[#0A140F] bg-[#F5F2EB] hover:bg-white active:scale-95 transition-all shadow-sm"
          >
            <span>Unduh APK</span>
            <Download className="w-3.5 h-3.5 stroke-[2.5]" />
          </a>
        </div>
      </div>
    </header>
  );
};

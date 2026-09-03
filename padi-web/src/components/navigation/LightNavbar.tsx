import React, { useState, useEffect } from 'react';
import { Sprout, Download, ArrowRight, Menu, X } from 'lucide-react';

export const LightNavbar: React.FC = () => {
  const [scrolled, setScrolled] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    const onScroll = () => {
      setScrolled(window.scrollY > 20);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  return (
    <header className="sticky top-0 left-0 w-full z-50 px-4 sm:px-8 pt-4 pb-2 transition-all">
      <div
        className={`max-w-6xl mx-auto rounded-full px-6 py-3.5 transition-all duration-300 flex items-center justify-between ${
          scrolled
            ? 'bg-white/95 backdrop-blur-md shadow-[0_8px_30px_rgba(0,0,0,0.06)] border border-gray-100'
            : 'bg-white/80 backdrop-blur-sm border border-gray-100/80 shadow-xs'
        }`}
      >
        {/* Left: Brand Logo */}
        <a href="#" className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-full bg-[#DCFCE7] flex items-center justify-center text-[#16A34A]">
            <Sprout className="w-4 h-4 stroke-[2.5]" />
          </div>
          <span className="text-lg font-black tracking-tight text-gray-900">
            P.A.D.I.
          </span>
        </a>

        {/* Center: Clean Nav Links */}
        <nav className="hidden md:flex items-center gap-8 text-sm font-semibold text-gray-600">
          <a href="#beranda" className="text-[#16A34A] transition-colors">
            Beranda
          </a>
          <a href="#fitur" className="hover:text-gray-900 transition-colors">
            Deteksi AI
          </a>
          <a href="#wawasan" className="hover:text-gray-900 transition-colors">
            Cuaca & Jadwal
          </a>
          <a href="#penyuluh" className="hover:text-gray-900 transition-colors">
            Penyuluh PPL
          </a>
        </nav>

        {/* Right: Join / Download Pill CTA */}
        <div className="flex items-center gap-3">
          <a
            href="/downloads/padi-latest.apk"
            download="PADI-latest.apk"
            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold text-white bg-[#16A34A] hover:bg-[#15803D] active:scale-95 transition-all shadow-[0_4px_14px_rgba(22,163,74,0.3)]"
          >
            <span>Unduh APK</span>
            <ArrowRight className="w-3.5 h-3.5" />
          </a>

          {/* Mobile menu trigger */}
          <button
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className="md:hidden p-2 rounded-full hover:bg-gray-100 text-gray-700"
            aria-label="Buka Menu"
          >
            {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>
        </div>
      </div>

      {/* Mobile Drawer */}
      {mobileMenuOpen && (
        <div className="md:hidden mt-2 max-w-6xl mx-auto bg-white rounded-3xl p-6 shadow-xl border border-gray-100 space-y-4">
          <nav className="flex flex-col gap-3 font-semibold text-gray-700 text-sm">
            <a
              href="#beranda"
              onClick={() => setMobileMenuOpen(false)}
              className="px-3 py-2 rounded-xl hover:bg-gray-50 text-[#16A34A]"
            >
              Beranda
            </a>
            <a
              href="#fitur"
              onClick={() => setMobileMenuOpen(false)}
              className="px-3 py-2 rounded-xl hover:bg-gray-50"
            >
              Deteksi AI
            </a>
            <a
              href="#wawasan"
              onClick={() => setMobileMenuOpen(false)}
              className="px-3 py-2 rounded-xl hover:bg-gray-50"
            >
              Cuaca & Jadwal
            </a>
            <a
              href="#penyuluh"
              onClick={() => setMobileMenuOpen(false)}
              className="px-3 py-2 rounded-xl hover:bg-gray-50"
            >
              Penyuluh PPL
            </a>
          </nav>
        </div>
      )}
    </header>
  );
};

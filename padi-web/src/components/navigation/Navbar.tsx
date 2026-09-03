import React, { useState, useEffect } from 'react';
import { Menu, Download, Sprout } from 'lucide-react';
import { MobileMenu } from './MobileMenu';

export const Navbar: React.FC = () => {
  const [scrolled, setScrolled] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    const onScroll = () => {
      setScrolled(window.scrollY > 30);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  return (
    <>
      <header
        className={`fixed top-0 left-0 w-full z-40 transition-all duration-300 ${
          scrolled
            ? 'bg-[#073D29]/90 backdrop-blur-md py-3 shadow-[0_10px_30px_rgba(0,0,0,0.3)] border-b border-white/10'
            : 'bg-transparent py-5'
        }`}
      >
        <div className="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between">
          {/* Brand Logo */}
          <a href="#" className="flex items-center gap-2.5 group">
            <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#0C7047] to-[#41A55B] border border-[#F5C842]/50 flex items-center justify-center text-white shadow-sm transition-transform group-hover:scale-105">
              <Sprout className="w-4 h-4 text-[#F5C842]" />
            </div>
            <div className="flex flex-col text-left">
              <span className="text-base font-black tracking-tight text-white leading-none">
                P.A.D.I.
              </span>
              <span className="text-[9px] font-medium text-white/60 tracking-wider">
                Agriculture Intelligence
              </span>
            </div>
          </a>

          {/* Desktop Navigation Links (Tentang, Fitur, Cara Kerja, Download) */}
          <nav className="hidden md:flex items-center gap-8 text-xs font-semibold text-white/80">
            <a href="#tentang" className="hover:text-[#F5C842] transition-colors">
              Tentang
            </a>
            <a href="#fitur" className="hover:text-[#F5C842] transition-colors">
              Fitur
            </a>
            <a href="#cara-kerja" className="hover:text-[#F5C842] transition-colors">
              Cara Kerja
            </a>
            <a href="#download" className="hover:text-[#F5C842] transition-colors">
              Download
            </a>
          </nav>

          {/* Actions: Download Button ALWAYS Available on Desktop & Mobile */}
          <div className="flex items-center gap-2 sm:gap-3">
            <a
              href="/downloads/padi-latest.apk"
              download="PADI-latest.apk"
              className="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 rounded-xl bg-[#F5C842] hover:bg-[#ebd056] active:scale-95 text-[#063D2B] font-black text-xs shadow-md transition-all whitespace-nowrap"
            >
              <Download className="w-3.5 h-3.5 stroke-[2.5]" />
              <span className="hidden xs:inline">Unduh</span>
              <span>APK</span>
            </a>

            {/* Mobile Hamburger Toggle */}
            <button
              type="button"
              onClick={() => setMobileMenuOpen(true)}
              className="md:hidden p-2 rounded-xl bg-white/10 text-white hover:bg-white/15 active:scale-95 transition-all"
              aria-label="Buka Menu"
            >
              <Menu className="w-5 h-5" />
            </button>
          </div>
        </div>
      </header>

      {/* Mobile Drawer Menu */}
      <MobileMenu
        isOpen={mobileMenuOpen}
        onClose={() => setMobileMenuOpen(false)}
      />
    </>
  );
};

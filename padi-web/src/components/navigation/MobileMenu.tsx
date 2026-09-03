import React from 'react';
import { X, Download, Sprout, ArrowRight } from 'lucide-react';

interface MobileMenuProps {
  isOpen: boolean;
  onClose: () => void;
}

export const MobileMenu: React.FC<MobileMenuProps> = ({ isOpen, onClose }) => {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 md:hidden flex flex-col justify-between bg-[#063D2B]/95 backdrop-blur-2xl p-6 text-white animate-fadeIn">
      {/* Top Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-xl bg-[#0C7047] border border-[#F5C842]/40 flex items-center justify-center text-white">
            <Sprout className="w-4 h-4 text-[#F5C842]" />
          </div>
          <span className="text-base font-black tracking-tight text-white">
            P.A.D.I.
          </span>
        </div>
        <button
          type="button"
          onClick={onClose}
          className="p-2 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 transition-all text-white"
          aria-label="Tutup Menu"
        >
          <X className="w-5 h-5" />
        </button>
      </div>

      {/* Menu Links: Tentang, Fitur, Cara Kerja, Download */}
      <nav className="flex flex-col gap-6 text-left my-auto">
        <a
          href="#tentang"
          onClick={onClose}
          className="text-2xl font-extrabold text-white/90 hover:text-[#F5C842] flex items-center justify-between border-b border-white/10 pb-3"
        >
          <span>Tentang P.A.D.I.</span>
          <ArrowRight className="w-5 h-5 text-white/40" />
        </a>
        <a
          href="#fitur"
          onClick={onClose}
          className="text-2xl font-extrabold text-white/90 hover:text-[#F5C842] flex items-center justify-between border-b border-white/10 pb-3"
        >
          <span>Fitur Intelijen</span>
          <ArrowRight className="w-5 h-5 text-white/40" />
        </a>
        <a
          href="#cara-kerja"
          onClick={onClose}
          className="text-2xl font-extrabold text-white/90 hover:text-[#F5C842] flex items-center justify-between border-b border-white/10 pb-3"
        >
          <span>Cara Kerja</span>
          <ArrowRight className="w-5 h-5 text-white/40" />
        </a>
        <a
          href="#download"
          onClick={onClose}
          className="text-2xl font-extrabold text-white/90 hover:text-[#F5C842] flex items-center justify-between border-b border-white/10 pb-3"
        >
          <span>Download APK</span>
          <ArrowRight className="w-5 h-5 text-white/40" />
        </a>
      </nav>

      {/* Bottom CTA */}
      <div className="space-y-3">
        <a
          href="/downloads/padi-latest.apk"
          download="PADI-latest.apk"
          onClick={onClose}
          className="w-full flex items-center justify-center gap-2 py-4 rounded-2xl bg-[#F5C842] text-[#063D2B] font-extrabold text-sm shadow-xl"
        >
          <Download className="w-4 h-4 stroke-[2.5]" />
          <span>Download APK Android (v1.0.0)</span>
        </a>
        <p className="text-[11px] text-white/50 text-center">
          Predictive Agriculture & Disease Intelligence — Polindra
        </p>
      </div>
    </div>
  );
};

import React from 'react';
import { Sprout, Heart, Shield } from 'lucide-react';

export const Footer: React.FC = () => {
  return (
    <footer className="w-full bg-[#030D09] text-white/70 py-16 px-4 sm:px-6 border-t border-white/10 text-left">
      <div className="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-10">
        {/* Brand & Mission */}
        <div className="md:col-span-6 space-y-3">
          <div className="flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-xl bg-[#0C7047] border border-[#F5C842]/40 flex items-center justify-center text-white">
              <Sprout className="w-4 h-4 text-[#F5C842]" />
            </div>
            <span className="text-lg font-black text-white tracking-tight">
              P.A.D.I.
            </span>
          </div>
          <p className="text-xs text-white/50 font-medium">
            Predictive Agriculture & Disease Intelligence
          </p>
          <p className="text-xs text-white/70 max-w-sm leading-relaxed">
            Platform intelijen keputusan pertanian padi berbasis AI, telemetri cuaca mikro, radar penyakit hamparan, dan kolaborasi terpadu penyuluh lapangan.
          </p>
          <div className="pt-1 text-[11px] text-[#F5C842] font-semibold">
            Politeknik Negeri Indramayu &bull; Team Fantastic
          </div>
        </div>

        {/* Quick Links */}
        <div className="md:col-span-3 space-y-3">
          <div className="text-xs font-bold text-white uppercase tracking-wider">
            Navigasi Halaman
          </div>
          <ul className="space-y-2 text-xs">
            <li>
              <a href="#masalah" className="hover:text-white transition-colors">
                Masalah Lapangan
              </a>
            </li>
            <li>
              <a href="#solusi" className="hover:text-white transition-colors">
                Solusi Cerdas P.A.D.I.
              </a>
            </li>
            <li>
              <a href="#fitur" className="hover:text-white transition-colors">
                AI Deteksi Penyakit Daun
              </a>
            </li>
            <li>
              <a href="#ekosistem" className="hover:text-white transition-colors">
                Ekosistem Terpadu
              </a>
            </li>
          </ul>
        </div>

        {/* Downloads & Technical */}
        <div className="md:col-span-3 space-y-3">
          <div className="text-xs font-bold text-white uppercase tracking-wider">
            Aplikasi Mobile
          </div>
          <ul className="space-y-2 text-xs">
            <li>
              <a
                href="/downloads/padi-latest.apk"
                download="PADI-latest.apk"
                className="hover:text-[#F5C842] font-semibold text-white transition-colors inline-flex items-center gap-1.5"
              >
                <span>Unduh APK Android (v1.0.0)</span>
              </a>
            </li>
            <li>
              <span className="text-white/40">Build KMIPN 2026</span>
            </li>
            <li>
              <span className="text-white/40">Minimum Android 8.0 Oreo</span>
            </li>
          </ul>
        </div>
      </div>

      <div className="max-w-6xl mx-auto pt-10 mt-10 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between text-xs text-white/40 gap-3">
        <div>
          &copy; {new Date().getFullYear()} P.A.D.I. All rights reserved. Dikembangkan untuk kedaulatan pangan Indonesia.
        </div>
        <div className="flex items-center gap-1 text-[11px]">
          <span>Dibuat dengan dedikasi di Indramayu</span>
        </div>
      </div>
    </footer>
  );
};

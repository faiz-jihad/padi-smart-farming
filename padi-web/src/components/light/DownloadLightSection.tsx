import React from 'react';
import { Download, ShieldCheck, Smartphone, ArrowRight } from 'lucide-react';

export const DownloadLightSection: React.FC = () => {
  return (
    <section id="penyuluh" className="relative w-full py-24 px-6 sm:px-12 md:px-20 bg-white text-center border-t border-gray-100">
      <div className="max-w-3xl mx-auto space-y-6">
        <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 text-[#16A34A] text-xs font-bold uppercase tracking-wider">
          Siap Memulai Musim Tanam Baru?
        </div>

        <h2 className="text-3xl sm:text-5xl md:text-6xl font-black text-gray-950 tracking-tight leading-tight">
          Mulai Kelola Sawah <br />
          Lebih Cerdas Hari Ini.
        </h2>

        <p className="text-base sm:text-lg text-gray-600 max-w-lg mx-auto leading-relaxed">
          Unduh aplikasi P.A.D.I. untuk smartphone Android Anda. Bebas biaya, ringan (~38 MB), dan langsung siap pakai di sawah.
        </p>

        {/* Large Rounded Action Card */}
        <div className="max-w-md mx-auto bg-gray-50 p-6 sm:p-8 rounded-[32px] border border-gray-200/80 shadow-lg space-y-5">
          <div className="flex items-center justify-between text-xs text-gray-500 pb-3 border-b border-gray-200">
            <span>Versi Resmi: <strong>v1.0.0</strong></span>
            <span>Android 8.0+</span>
            <span>Ukuran: <strong>~38 MB</strong></span>
          </div>

          <a
            href="/downloads/padi-latest.apk"
            download="PADI-latest.apk"
            className="w-full inline-flex items-center justify-center gap-3 py-4 px-6 rounded-full bg-[#16A34A] hover:bg-[#15803D] active:scale-95 text-white font-bold text-sm sm:text-base shadow-[0_8px_25px_rgba(22,163,74,0.35)] transition-all"
          >
            <Download className="w-5 h-5 stroke-[2.5]" />
            <span>Unduh Berkas APK Sekarang</span>
          </a>

          <div className="flex items-center justify-center gap-2 text-[11px] text-gray-400">
            <ShieldCheck className="w-4 h-4 text-emerald-600" />
            <span>Terverifikasi Bebas Malware &bull; Kompetisi KMIPN VI</span>
          </div>
        </div>
      </div>
    </section>
  );
};

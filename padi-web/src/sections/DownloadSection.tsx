import React from 'react';
import { DownloadAPK } from '../components/download/DownloadAPK';
import { Smartphone, Download, Sparkles } from 'lucide-react';

export const DownloadSection: React.FC = () => {
  return (
    <section
      id="download"
      className="relative w-full min-h-screen bg-gradient-to-b from-[#04140D] via-[#062B1D] to-[#04120D] text-white py-28 px-4 sm:px-6 flex flex-col items-center justify-center text-center overflow-hidden border-t border-white/5"
    >
      {/* Background Glow */}
      <div className="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#41A55B]/10 rounded-full blur-[120px] pointer-events-none" />

      <div className="max-w-4xl mx-auto space-y-4 mb-10 relative z-10">
        <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#0C7047] text-[#F5C842] text-xs font-black uppercase tracking-wider border border-[#41A55B]/40">
          <Smartphone className="w-3.5 h-3.5" />
          <span>Aplikasi Mobile Android</span>
        </div>

        <h2 className="text-4xl sm:text-5xl md:text-6xl font-black text-white tracking-tight leading-tight">
          Saatnya Petani <br />
          <span className="text-[#F5C842]">Memegang Kendali.</span>
        </h2>

        <p className="text-sm sm:text-base md:text-lg text-white/80 max-w-lg mx-auto leading-relaxed">
          Semua kecerdasan buatan, prakiraan cuaca jam-jaman, dan radar komunitas kini siap bekerja bersama Anda di tengah hamparan sawah.
        </p>
      </div>

      {/* Main Download Component */}
      <div className="relative z-10 w-full">
        <DownloadAPK />
      </div>
    </section>
  );
};

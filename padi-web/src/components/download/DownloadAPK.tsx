import React, { useState } from 'react';
import { Download, Smartphone, ShieldCheck, Check, FileText, ChevronDown, ChevronUp, AlertCircle } from 'lucide-react';

export const DownloadAPK: React.FC = () => {
  const [downloading, setDownloading] = useState(false);
  const [showReleaseNotes, setShowReleaseNotes] = useState(false);

  const handleDownload = () => {
    setDownloading(true);
    setTimeout(() => {
      setDownloading(false);
    }, 4000);
  };

  const currentDate = new Date().toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });

  return (
    <div className="w-full max-w-xl mx-auto text-left space-y-5">
      {/* Main Download Card */}
      <div className="bg-gradient-to-br from-[#063D2B] via-[#075B3B] to-[#0A1F16] p-6 sm:p-8 rounded-[32px] border-2 border-[#41A55B]/40 shadow-[0_20px_50px_rgba(6,61,43,0.5)] text-center relative overflow-hidden">
        {/* Decorative Background Rice Glow */}
        <div className="absolute -top-12 -right-12 w-48 h-48 bg-[#F5C842]/10 rounded-full blur-3xl pointer-events-none" />

        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#0C7047] text-white text-xs font-extrabold mb-4 border border-[#41A55B]/40 shadow-sm">
          <Smartphone className="w-3.5 h-3.5 text-[#F5C842]" />
          <span>Rilis Publik Resmi Android</span>
        </div>

        <h3 className="text-2xl sm:text-3xl font-black text-white tracking-tight leading-tight">
          Mulai Kelola Sawah Dengan Lebih Cerdas
        </h3>

        <p className="text-sm sm:text-base text-white/80 mt-2 max-w-md mx-auto leading-relaxed">
          Unduh aplikasi P.A.D.I. untuk ponsel Android Anda dan dapatkan analisis data presisi langsung di genggaman.
        </p>

        {/* Primary Download Anchor Button */}
        <div className="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
          <a
            href="/downloads/padi-latest.apk"
            download="PADI-latest.apk"
            onClick={handleDownload}
            className="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-[#F5C842] hover:bg-[#ebd056] active:scale-[0.98] text-[#063D2B] font-black text-base shadow-xl hover:shadow-[0_0_30px_rgba(245,200,66,0.4)] transition-all"
          >
            <Download className="w-5 h-5 stroke-[2.5]" />
            <span>{downloading ? 'Mengunduh Paket APK...' : 'Download P.A.D.I. APK'}</span>
          </a>

          <button
            type="button"
            onClick={() => setShowReleaseNotes(!showReleaseNotes)}
            className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-2xl bg-white/10 hover:bg-white/15 text-white text-sm font-bold border border-white/15 transition-all"
          >
            <FileText className="w-4 h-4 text-[#F5C842]" />
            <span>Release Notes</span>
            {showReleaseNotes ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
          </button>
        </div>

        {/* Download Feedback Banner */}
        {downloading && (
          <div className="mt-4 p-2.5 rounded-xl bg-[#41A55B]/20 border border-[#41A55B]/40 text-xs text-[#F5F2E9] font-medium animate-fadeIn">
            ✓ Download P.A.D.I. dimulai. Silakan cek panel notifikasi unduhan browser Anda.
          </div>
        )}

        {/* Technical Specification Grid */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-6 mt-6 border-t border-white/10 text-xs text-white/70">
          <div>
            <span className="text-white/40 block text-[10px]">Versi Aplikasi</span>
            <strong className="text-white font-bold text-xs">v1.0.0 (KMIPN)</strong>
          </div>
          <div>
            <span className="text-white/40 block text-[10px]">Ukuran File</span>
            <strong className="text-white font-bold text-xs">~38.4 MB</strong>
          </div>
          <div>
            <span className="text-white/40 block text-[10px]">OS Minimum</span>
            <strong className="text-white font-bold text-xs">Android 8.0+</strong>
          </div>
          <div>
            <span className="text-white/40 block text-[10px]">Pembaruan</span>
            <strong className="text-white font-bold text-xs">{currentDate}</strong>
          </div>
        </div>
      </div>

      {/* Release Notes Collapsible Sheet */}
      {showReleaseNotes && (
        <div className="bg-[#0D1C15] p-5 rounded-2xl border border-white/10 text-xs text-white/80 space-y-3 animate-fadeIn">
          <div className="flex items-center justify-between border-b border-white/10 pb-2">
            <h4 className="font-extrabold text-white text-sm">Catatan Rilis v1.0.0</h4>
            <span className="text-[10px] text-[#F5C842] font-semibold">Build Produksi</span>
          </div>
          <ul className="space-y-2 list-disc pl-4 leading-relaxed">
            <li><strong className="text-white">AI Scanner YOLO11:</strong> Deteksi penyakit bercak daun, hawar pelepah, dan hawar bakteri dengan model luring/daring.</li>
            <li><strong className="text-white">PPL Field Integration:</strong> Fitur eskalasi kasus mandiri petani ke penyuluh lapangan BPP Indramayu.</li>
            <li><strong className="text-white">Disease Radar & Early Warning:</strong> Peta sebaran penyakit hamparan berbasis komunitas.</li>
            <li><strong className="text-white">Smart Irrigation & Weather:</strong> Rekomendasi jam pemupukan dan gilir air irigasi petak sawah.</li>
          </ul>
        </div>
      )}

      {/* Honest & Helpful Third-Party Installation Guide */}
      <div className="bg-[#0A1A12] p-4 rounded-2xl border border-white/10 flex items-start gap-3 text-xs text-white/70 leading-relaxed">
        <AlertCircle className="w-5 h-5 text-[#F5C842] shrink-0 mt-0.5" />
        <div>
          <strong className="text-white font-semibold">Panduan Instalasi APK Android:</strong>
          <p className="mt-0.5 text-white/60">
            Jika muncul peringatan instalasi dari sumber di luar Google Play Store, pilih <span className="text-white underline">Pengaturan &rarr; Izinkan instalasi aplikasi dari sumber ini</span> pada browser (Chrome / Edge) yang Anda gunakan.
          </p>
        </div>
      </div>
    </div>
  );
};

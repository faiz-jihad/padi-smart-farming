import React from 'react';
import { AlertCircle, CheckCircle2, ChevronRight, Info } from 'lucide-react';

export const ResultScreen: React.FC = () => {
  return (
    <div className="p-4 space-y-3.5 text-left text-white select-none">
      {/* Top Breadcrumb */}
      <div className="flex items-center justify-between text-[11px] text-white/50 pt-1">
        <span>Hasil Deteksi Daun</span>
        <span className="text-emerald-400 font-semibold">Tersimpan</span>
      </div>

      {/* Main Diagnosis Card */}
      <div className="bg-[#14231A] p-4 rounded-2xl border border-white/10 space-y-3">
        <div>
          <span className="text-[10px] text-white/50 uppercase tracking-wider font-semibold">
            Penyakit Teridentifikasi
          </span>
          <h3 className="text-base font-bold text-white leading-tight mt-0.5">
            Hawar Daun Bakteri
          </h3>
          <p className="text-[11px] text-white/50 italic">
            Xanthomonas oryzae pv. oryzae
          </p>
        </div>

        {/* Metrics Grid */}
        <div className="grid grid-cols-2 gap-2 pt-2 border-t border-white/5 text-xs">
          <div className="bg-black/20 p-2 rounded-xl">
            <span className="text-white/50 text-[10px] block">Tingkat Keyakinan</span>
            <strong className="text-[#D4A017] text-sm font-bold">94.7%</strong>
          </div>
          <div className="bg-black/20 p-2 rounded-xl">
            <span className="text-white/50 text-[10px] block">Tingkat Risiko</span>
            <strong className="text-amber-400 text-sm font-bold">Sedang</strong>
          </div>
        </div>

        {/* Honest Grounded Disclaimer */}
        <div className="bg-black/30 p-2.5 rounded-xl border border-white/5 text-[10px] text-white/70 leading-relaxed">
          <p className="font-semibold text-white">Prediksi awal berbasis AI.</p>
          <p className="mt-0.5 text-white/50">
            Jika diperlukan, hasil dapat diteruskan untuk validasi penyuluh lapangan.
          </p>
        </div>
      </div>

      {/* Direct Action Link */}
      <div className="bg-[#18261E] p-3 rounded-xl border border-white/10 flex items-center justify-between">
        <div>
          <span className="text-xs font-bold text-white block">Lihat Rekomendasi Tindakan</span>
          <span className="text-[10px] text-white/50">3 langkah penanganan lapangan</span>
        </div>
        <ChevronRight className="w-4 h-4 text-white/50" />
      </div>
    </div>
  );
};

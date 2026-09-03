import React from 'react';
import { FarmRecordSheet } from '../components/padi/FarmRecordSheet';
import { BookOpen, TrendingUp, History } from 'lucide-react';

export const FarmRecordStory: React.FC = () => {
  return (
    <section className="relative w-full bg-[#0A1F16] text-white py-24 px-4 sm:px-6 flex items-center justify-center overflow-hidden border-t border-white/5">
      <div className="max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center text-left">
        {/* Left: Narrative Philosophy */}
        <div className="lg:col-span-6 space-y-4">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#075B3B] text-[#41A55B] text-xs font-bold border border-[#41A55B]/30">
            <History className="w-3.5 h-3.5 text-[#F5C842]" />
            <span>Riwayat Agronomi Sepanjang Musim</span>
          </div>

          <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.15]">
            Setiap musim <br />
            <span className="text-[#F5C842]">menjadi data.</span>
          </h2>

          <p className="text-lg sm:text-xl font-extrabold text-[#41A55B] leading-snug">
            Data menjadi pengalaman. Pengalaman menjadi keputusan yang lebih baik.
          </p>

          <p className="text-sm sm:text-base text-white/80 leading-relaxed max-w-lg">
            Tidak ada lagi catatan yang tercecer di buku kertas yang basah. Dari pemupukan pertama hingga saat panen raya tiba, seluruh riwayat agronomi tercatat rapi dan dapat ditinjau kembali untuk perencanaan musim tanam berikutnya.
          </p>
        </div>

        {/* Right: Digital Farm Record Sheet */}
        <div className="lg:col-span-6 flex justify-center">
          <div className="w-full max-w-sm">
            <FarmRecordSheet />
          </div>
        </div>
      </div>
    </section>
  );
};

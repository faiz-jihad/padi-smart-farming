import React from 'react';
import { PhoneMockup } from '../components/padi/PhoneMockup';
import { AIPlantCheck } from '../components/padi/AIPlantCheck';
import { Scan, Sparkles, ShieldCheck, Cpu } from 'lucide-react';

export const AIStory: React.FC = () => {
  return (
    <section
      id="fitur"
      className="relative w-full min-h-screen bg-[#071710] text-white py-24 px-4 sm:px-6 flex items-center justify-center overflow-hidden"
    >
      {/* Laser Glow Effect Background */}
      <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-[#41A55B]/10 rounded-full blur-[100px] pointer-events-none" />

      <div className="max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center text-left">
        {/* Left: Headline & AI Philosophy */}
        <div className="lg:col-span-6 space-y-5">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#0C7047] text-[#F5C842] text-xs font-black uppercase tracking-wider border border-[#41A55B]/30">
            <Cpu className="w-3.5 h-3.5" />
            <span>AI Computer Vision (YOLO11)</span>
          </div>

          <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black tracking-tight text-white leading-[1.15]">
            Lihat gejalanya. <br />
            <span className="text-[#41A55B]">Pahami masalahnya.</span>
          </h2>

          <p className="text-sm sm:text-base md:text-lg text-white/80 leading-relaxed max-w-lg">
            Arahkan kamera ke helai daun padi yang mencurigakan. Dalam hitungan milidetik, model AI kami mengidentifikasi pola lesi patogen dan menghitung estimasi keparahan secara transparan.
          </p>

          {/* Key Value Points */}
          <div className="space-y-3 pt-2">
            <div className="bg-[#0A2217] p-3.5 rounded-2xl border border-white/10 space-y-1">
              <div className="flex items-center gap-2 text-xs font-bold text-[#F5C842]">
                <Sparkles className="w-4 h-4" />
                <span>Transparansi Skor Keyakinan</span>
              </div>
              <p className="text-xs text-white/70 leading-relaxed">
                P.A.D.I. tidak pernah berpura-pura tahu segalanya. Setiap deteksi dilengkapi persentase akurasi model dan area daun terdampak.
              </p>
            </div>

            <div className="bg-[#0A2217] p-3.5 rounded-2xl border border-white/10 space-y-1">
              <div className="flex items-center gap-2 text-xs font-bold text-white">
                <ShieldCheck className="w-4 h-4 text-[#41A55B]" />
                <span>Dukungan Mode Luring (Offline First)</span>
              </div>
              <p className="text-xs text-white/70 leading-relaxed">
                Petani tetap dapat memotret di tengah pematang sawah tanpa sinyal. Diagnosa diproses lokal dan disinkronkan saat kembali online.
              </p>
            </div>
          </div>
        </div>

        {/* Right: Phone Scanner Active State */}
        <div className="lg:col-span-6 flex justify-center">
          <PhoneMockup>
            <AIPlantCheck scanned={true} />
          </PhoneMockup>
        </div>
      </div>
    </section>
  );
};

import React, { useRef } from 'react';
import { StickyStory } from '../components/scrollytelling/StickyStory';
import { PhoneMockup } from '../components/padi/PhoneMockup';
import { FarmPriority } from '../components/padi/FarmPriority';
import { CheckCircle2, CalendarDays, Sparkles } from 'lucide-react';

export const FarmPriorityStory: React.FC = () => {
  return (
    <StickyStory heightViewport={2.2} className="bg-[#0A1F16] text-white">
      {() => (
        <div className="w-full max-w-6xl mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center text-left">
          {/* Left: Editorial Narrative Column */}
          <div className="lg:col-span-6 space-y-5">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#0C7047] text-white text-xs font-bold border border-[#41A55B]/40">
              <CalendarDays className="w-3.5 h-3.5 text-[#F5C842]" />
              <span>Prioritas Harian Terkurasi</span>
            </div>

            <h2 className="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.15]">
              Buka aplikasi. <br />
              <span className="text-[#F5C842]">Tahu apa yang harus dilakukan.</span>
            </h2>

            <p className="text-sm sm:text-base md:text-lg text-white/80 leading-relaxed max-w-lg">
              P.A.D.I. menyatukan kondisi kesehatan daun tanaman, prakiraan cuaca jam-jaman, usia HST musim tanam, dan catatan lapangan menjadi prioritas harian yang langsung bisa dieksekusi oleh petani.
            </p>

            <div className="space-y-3 pt-2">
              <div className="flex items-start gap-3">
                <div className="w-5 h-5 rounded-full bg-[#41A55B]/20 text-[#41A55B] flex items-center justify-center shrink-0 mt-0.5">
                  <CheckCircle2 className="w-3.5 h-3.5" />
                </div>
                <p className="text-xs sm:text-sm text-white/80">
                  <strong className="text-white">Bukan tumpukan grafik rumit:</strong> Petani mendapatkan arahan verbal bahasa Indonesia praktis.
                </p>
              </div>

              <div className="flex items-start gap-3">
                <div className="w-5 h-5 rounded-full bg-[#41A55B]/20 text-[#41A55B] flex items-center justify-center shrink-0 mt-0.5">
                  <CheckCircle2 className="w-3.5 h-3.5" />
                </div>
                <p className="text-xs sm:text-sm text-white/80">
                  <strong className="text-white">Efisiensi biaya saprotan:</strong> Mencegah pemupukan saat hujan lebat atau angin kencang.
                </p>
              </div>
            </div>
          </div>

          {/* Right: Sticky Phone Mockup */}
          <div className="lg:col-span-6 flex justify-center">
            <PhoneMockup>
              <FarmPriority />
            </PhoneMockup>
          </div>
        </div>
      )}
    </StickyStory>
  );
};

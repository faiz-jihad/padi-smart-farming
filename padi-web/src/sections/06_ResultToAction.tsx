import React from 'react';
import { RealPhoneFrame } from '../components/phone/RealPhoneFrame';
import { ActionScreen } from '../components/phone/screens/ActionScreen';

export const ResultToAction: React.FC = () => {
  return (
    <section
      id="cara-kerja"
      className="relative w-full bg-[#0C1E15] text-white py-28 px-6 sm:px-12 flex flex-col items-center justify-center text-center overflow-hidden border-t border-white/5"
    >
      <div className="max-w-2xl mx-auto space-y-4 mb-12 text-center">
        <h2 className="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-tight">
          Mengetahui masalah belum cukup. <br />
          <span className="text-[#D4A017]">Tindakan berikutnya yang lebih penting.</span>
        </h2>

        <p className="text-sm sm:text-base text-white/70 max-w-md mx-auto leading-relaxed">
          P.A.D.I. memberikan panduan langkah demi langkah yang dapat langsung diterapkan petani di pematang sawah.
        </p>
      </div>

      {/* Real Phone Recommendation Showcase */}
      <div className="w-full flex justify-center">
        <RealPhoneFrame>
          <ActionScreen />
        </RealPhoneFrame>
      </div>
    </section>
  );
};

import React from 'react';
import { Download, ShieldCheck } from 'lucide-react';
import { RealPhoneFrame } from '../components/phone/RealPhoneFrame';
import { HomeScreen } from '../components/phone/screens/HomeScreen';

export const DownloadSection: React.FC = () => {
  return (
    <section
      id="download"
      className="relative w-full min-h-screen bg-[#07150E] text-[#F5F2EB] py-28 px-6 sm:px-12 flex flex-col items-center justify-center text-center overflow-hidden border-t border-white/10"
    >
      <div className="max-w-2xl mx-auto space-y-4 mb-10">
        <h2 className="text-4xl sm:text-6xl md:text-7xl font-black tracking-tight text-white">
          P.A.D.I.
        </h2>

        <p className="text-lg sm:text-2xl text-[#C9A96E] font-semibold leading-snug">
          Mulai kelola sawah dengan informasi yang lebih jelas.
        </p>

        <p className="text-sm sm:text-base text-white/70 max-w-md mx-auto leading-relaxed">
          Tersedia untuk ponsel Android. Unduh berkas instalasi langsung dan pasang di ponsel Anda tanpa perantara.
        </p>
      </div>

      {/* Real Phone Showcase */}
      <div className="mb-10 scale-95 sm:scale-100">
        <RealPhoneFrame>
          <HomeScreen />
        </RealPhoneFrame>
      </div>

      {/* Clean, Trustworthy Download Card */}
      <div className="w-full max-w-sm mx-auto space-y-4">
        <div className="space-y-1">
          <div className="text-sm font-bold text-white">P.A.D.I. untuk Android</div>
          <div className="text-xs text-white/50">Versi 1.0.0 &bull; Ukuran berkas ~38 MB</div>
        </div>

        {/* Real HTML Anchor Download Button */}
        <a
          href="/downloads/padi-latest.apk"
          download="PADI-latest.apk"
          className="w-full inline-flex items-center justify-center gap-2.5 py-4 px-6 rounded-full bg-[#F5F2EB] hover:bg-white active:scale-95 text-[#07150E] font-bold text-sm shadow-lg transition-all"
        >
          <Download className="w-4 h-4 stroke-[2.5]" />
          <span>Unduh Berkas APK Sekarang</span>
        </a>

        <div className="text-xs text-white/50 leading-relaxed pt-1">
          Memerlukan Android 8.0 atau yang lebih baru. <br />
          Jika peramban Anda meminta izin untuk mengunduh, pilih izinkan.
        </div>
      </div>
    </section>
  );
};

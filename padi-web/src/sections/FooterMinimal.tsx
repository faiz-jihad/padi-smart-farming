import React from 'react';

export const FooterMinimal: React.FC = () => {
  return (
    <footer className="w-full bg-[#04140D] text-white/40 py-12 px-6 border-t border-white/5 text-center text-xs space-y-2">
      <div className="flex items-center justify-center gap-2 font-medium text-white/60">
        <span>P.A.D.I.</span>
        <span>&bull;</span>
        <span>Politeknik Negeri Indramayu</span>
        <span>&bull;</span>
        <span>Team Fantastic</span>
      </div>
      <p className="text-[11px] text-white/30">
        Dikembangkan untuk mendukung ketepatan keputusan petani padi di lapangan.
      </p>
    </footer>
  );
};

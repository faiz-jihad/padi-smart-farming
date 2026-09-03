import React from 'react';
import { Sprout } from 'lucide-react';

export const LightFooter: React.FC = () => {
  return (
    <footer className="w-full bg-[#F8FAF8] border-t border-gray-200/80 py-12 px-6 sm:px-12 text-center text-xs text-gray-500 space-y-4">
      <div className="flex items-center justify-center gap-2">
        <div className="w-6 h-6 rounded-full bg-[#DCFCE7] flex items-center justify-center text-[#16A34A]">
          <Sprout className="w-3.5 h-3.5" />
        </div>
        <span className="font-bold text-gray-900 text-sm">P.A.D.I.</span>
      </div>

      <p className="text-gray-500 max-w-sm mx-auto leading-relaxed">
        Predictive Agriculture & Disease Intelligence &bull; Politeknik Negeri Indramayu &bull; Team Fantastic KMIPN VI
      </p>

      <div className="text-[11px] text-gray-400">
        &copy; 2026 P.A.D.I. Seluruh hak cipta dilindungi undang-undang.
      </div>
    </footer>
  );
};

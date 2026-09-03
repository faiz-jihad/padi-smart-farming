import React from 'react';
import { Sprout, Check, Calendar } from 'lucide-react';

export const CropCycleScreen: React.FC = () => {
  const steps = [
    { title: 'Tanam', hst: '0 HST', done: true },
    { title: 'Vegetatif Awal', hst: '20 HST', done: true },
    { title: 'Fase Anakan', hst: '45 HST', active: true },
    { title: 'Pembungaan', hst: '75 HST' },
    { title: 'Pengisian Bulir', hst: '95 HST' },
    { title: 'Panen', hst: '115 HST' },
  ];

  return (
    <div className="p-4 space-y-3.5 text-left text-white select-none">
      <div className="pt-1">
        <span className="text-[10px] text-white/50 uppercase tracking-wider font-semibold">
          Kalender Musim Tanam
        </span>
        <h3 className="text-sm font-bold text-white">Sawah Blok A &bull; Inpari 32</h3>
      </div>

      {/* Active Phase Card */}
      <div className="bg-[#14231A] p-3.5 rounded-2xl border border-white/10 space-y-2">
        <div className="flex items-center justify-between">
          <div>
            <span className="text-xl font-bold text-[#D4A017]">45 HST</span>
            <span className="text-xs text-white/70 block">Fase Anakan Maksimum</span>
          </div>
          <div className="w-8 h-8 rounded-full bg-[#18261E] border border-white/10 flex items-center justify-center text-[#2A7246]">
            <Sprout className="w-4 h-4" />
          </div>
        </div>

        <div className="bg-[#18261E] p-2.5 rounded-xl border border-white/5 text-xs text-white/70">
          <span className="text-[10px] text-white/40 block">Agenda Berikutnya:</span>
          <strong className="text-white">Pemupukan Tahap II</strong> (3 hari lagi)
        </div>
      </div>

      {/* Vertical Phase List */}
      <div className="space-y-1.5 pt-1">
        {steps.map((s) => (
          <div
            key={s.title}
            className={`p-2 rounded-xl flex items-center justify-between text-xs border ${
              s.active
                ? 'bg-[#18261E] border-[#D4A017]/40 text-white font-semibold'
                : s.done
                ? 'bg-[#121E17] border-white/5 text-white/60'
                : 'bg-transparent border-transparent text-white/30'
            }`}
          >
            <span>{s.title}</span>
            <div className="flex items-center gap-1.5">
              <span className="text-[10px]">{s.hst}</span>
              {s.done && <Check className="w-3 h-3 text-emerald-400" />}
              {s.active && <span className="w-1.5 h-1.5 rounded-full bg-[#D4A017]" />}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

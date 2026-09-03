import React from 'react';
import { Sprout, Calendar, Clock, ChevronRight, Check } from 'lucide-react';

interface CropTimelineProps {
  currentHst?: number;
}

export const CropTimeline: React.FC<CropTimelineProps> = ({ currentHst = 45 }) => {
  const stages = [
    { title: 'Tanam', hst: '0 HST', status: 'done', desc: 'Bibit pindah tanam' },
    { title: 'Vegetatif', hst: '15-30 HST', status: 'done', desc: 'Perakaran & daun awal' },
    { title: 'Anakan Aktif', hst: '45 HST', status: 'current', desc: 'Pembentukan anakan produktif' },
    { title: 'Pembungaan', hst: '65-80 HST', status: 'upcoming', desc: 'Malai bunting & mekar' },
    { title: 'Pengisian Bulir', hst: '90-105 HST', status: 'upcoming', desc: 'Pengerasan gabah susu' },
    { title: 'Panen', hst: '115 HST', status: 'upcoming', desc: 'Kadar air optimal 22%' },
  ];

  return (
    <div className="w-full max-w-2xl mx-auto space-y-4 text-left">
      {/* Active Phase Banner */}
      <div className="bg-gradient-to-r from-[#075B3B] to-[#063D2B] p-4 rounded-2xl border border-[#41A55B]/40 shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-[#F5C842] text-[#063D2B] font-extrabold flex items-center justify-center shrink-0 shadow">
            <Sprout className="w-5 h-5" />
          </div>
          <div>
            <div className="text-[11px] text-[#F5C842] font-extrabold uppercase tracking-wider">
              Status Pertumbuhan Sawah Blok A
            </div>
            <h3 className="text-lg font-extrabold text-white">
              {currentHst} HST — Fase Anakan Maksimum
            </h3>
          </div>
        </div>

        <div className="bg-[#042014] px-3 py-1.5 rounded-xl border border-white/10 text-xs text-white/80">
          <span className="text-white/50 text-[10px] block">Agenda Terdekat:</span>
          <strong className="text-[#F5C842]">Pemupukan Tahap II</strong> (3 hari lagi)
        </div>
      </div>

      {/* Horizontal Scrollable Timeline Bar */}
      <div className="overflow-x-auto pb-2 scrollbar-thin">
        <div className="flex items-center gap-3 min-w-[560px] px-1">
          {stages.map((stage, idx) => {
            const isCurrent = stage.status === 'current';
            const isDone = stage.status === 'done';

            return (
              <div
                key={stage.title}
                className={`flex-1 p-3 rounded-xl border transition-all ${
                  isCurrent
                    ? 'bg-[#075B3B] border-[#F5C842] shadow-md ring-2 ring-[#F5C842]/20'
                    : isDone
                    ? 'bg-[#12241C] border-[#41A55B]/30'
                    : 'bg-[#0A1A12] border-white/5 opacity-60'
                }`}
              >
                <div className="flex items-center justify-between mb-1">
                  <span
                    className={`text-[10px] font-bold px-1.5 py-0.5 rounded ${
                      isCurrent
                        ? 'bg-[#F5C842] text-[#063D2B]'
                        : isDone
                        ? 'bg-[#41A55B]/20 text-[#41A55B]'
                        : 'bg-white/10 text-white/50'
                    }`}
                  >
                    {stage.hst}
                  </span>
                  {isDone && <Check className="w-3 h-3 text-[#41A55B]" />}
                  {isCurrent && <span className="w-2 h-2 rounded-full bg-[#F5C842] animate-ping" />}
                </div>
                <div className="text-xs font-bold text-white leading-tight">{stage.title}</div>
                <div className="text-[10px] text-white/60 mt-0.5 leading-snug">{stage.desc}</div>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
};

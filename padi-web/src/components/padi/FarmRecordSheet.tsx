import React from 'react';
import { BookOpen, Calendar, CheckCircle2, Scan, ShieldCheck, Sprout, Droplet } from 'lucide-react';

export const FarmRecordSheet: React.FC = () => {
  const logs = [
    { date: '15 Feb', title: 'Perawatan & Drainase', icon: Droplet, desc: 'Pengeringan macak-macak pasca validasi penyakit', color: 'text-sky-400 bg-sky-500/15' },
    { date: '11 Feb', title: 'Validasi Petugas PPL', icon: ShieldCheck, desc: 'Dikonfirmasi Hawar Daun Bakteri oleh BPP Sindang', color: 'text-[#41A55B] bg-[#41A55B]/15' },
    { date: '11 Feb', title: 'AI Plant Check Scan', icon: Scan, desc: 'Deteksi mandiri petani via kamera smartphone (94.7%)', color: 'text-[#F5C842] bg-[#F5C842]/15' },
    { date: '26 Jan', title: 'Pemupukan Dasar', icon: Sprout, desc: 'Aplikasi NPK 15-15-15 (150 kg/Ha) pada pagi hari', color: 'text-emerald-400 bg-emerald-500/15' },
    { date: '12 Jan', title: 'Awal Musim Tanam', icon: Calendar, desc: 'Pindah tanam varietas Inpari 32 (Luas 1.2 Hektar)', color: 'text-white bg-white/10' },
  ];

  return (
    <div className="bg-[#12241C] p-4 rounded-3xl border border-[#41A55B]/40 shadow-xl text-left max-w-sm mx-auto space-y-3.5">
      <div className="flex items-center justify-between border-b border-white/10 pb-2.5">
        <div className="flex items-center gap-2">
          <div className="w-8 h-8 rounded-xl bg-[#075B3B] text-[#F5C842] flex items-center justify-center">
            <BookOpen className="w-4 h-4" />
          </div>
          <div>
            <span className="text-[10px] text-[#41A55B] font-bold uppercase tracking-wider">Log Agronomi Digital</span>
            <h4 className="text-sm font-extrabold text-white">Sawah Blok A (1.2 Ha)</h4>
          </div>
        </div>
        <span className="text-[10px] px-2 py-0.5 rounded bg-white/10 text-white font-bold">
          Musim 2026
        </span>
      </div>

      {/* Timeline Entries */}
      <div className="relative pl-3 space-y-3 before:absolute before:left-6 before:top-2 before:bottom-2 before:w-0.5 before:bg-white/10">
        {logs.map((log) => {
          const Icon = log.icon;
          return (
            <div key={log.title + log.date} className="relative flex items-start gap-3">
              <div className={`w-7 h-7 rounded-lg ${log.color} flex items-center justify-center shrink-0 relative z-10 border border-white/10`}>
                <Icon className="w-3.5 h-3.5" />
              </div>
              <div className="flex-1 min-w-0">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-bold text-white leading-tight">{log.title}</span>
                  <span className="text-[10px] text-white/50">{log.date}</span>
                </div>
                <p className="text-[11px] text-white/60 leading-snug mt-0.5">{log.desc}</p>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

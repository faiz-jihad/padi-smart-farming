import React from 'react';

export const FarmHistory: React.FC = () => {
  const events = [
    { date: '12 Jan', title: 'Mulai tanam', desc: 'Pindah tanam varietas Inpari 32 di petak Blok A.' },
    { date: '26 Jan', title: 'Pemupukan dasar', desc: 'Aplikasi pupuk NPK sesuai dosis anjuran.' },
    { date: '11 Feb', title: 'Scan daun', desc: 'Deteksi mandiri: indikasi Hawar Daun Bakteri (94.7%).' },
    { date: '11 Feb', title: 'Validasi PPL', desc: 'Dikonfirmasi oleh penyuluh lapangan BPP setempat.' },
    { date: '15 Feb', title: 'Penanganan', desc: 'Pengeringan petak macak-macak dan penyesuaian nitrogen.' },
  ];

  return (
    <section className="relative w-full bg-[#F7F5F0] text-[#141A16] py-28 px-6 sm:px-12 flex flex-col items-center justify-center border-t border-[#E5DFD3]">
      <div className="max-w-xl mx-auto text-left w-full space-y-8">
        <div className="space-y-2">
          <span className="text-xs font-semibold uppercase tracking-widest text-[#0C3825]/60">
            Riwayat Lahan
          </span>
          <h2 className="text-3xl sm:text-4xl md:text-5xl font-extrabold text-[#0C3825] tracking-tight leading-tight">
            Setiap musim <br />
            meninggalkan catatan.
          </h2>
          <p className="text-sm sm:text-base text-[#141A16]/70 leading-relaxed pt-1">
            Catatan itu membantu keputusan pada musim berikutnya.
          </p>
        </div>

        {/* Simple Vertical Timeline (No Card Grid!) */}
        <div className="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-[1.5px] before:bg-[#0C3825]/20">
          {events.map((e) => (
            <div key={e.date + e.title} className="relative flex items-start gap-4">
              {/* Dot node */}
              <div className="absolute -left-[21px] top-1.5 w-2.5 h-2.5 rounded-full bg-[#0C3825] border-2 border-[#F7F5F0]" />

              <div className="text-left">
                <div className="flex items-baseline gap-2">
                  <span className="text-xs font-bold text-[#D4A017]">{e.date}</span>
                  <span className="text-sm font-bold text-[#0C3825]">{e.title}</span>
                </div>
                <p className="text-xs text-[#141A16]/65 mt-0.5 leading-snug">
                  {e.desc}
                </p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

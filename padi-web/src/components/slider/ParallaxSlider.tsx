import React, { useRef, useState, useEffect } from 'react';
import { ArrowLeft, ArrowRight, Sprout, CloudSun, ShieldCheck, Radio, UserCheck } from 'lucide-react';

interface StoryCard {
  id: string;
  tag: string;
  title: string;
  description: string;
  image: string;
  badgeLabel: string;
  badgeValue: string;
  badgeIcon: React.ReactNode;
}

export const ParallaxSlider: React.FC = () => {
  const scrollContainerRef = useRef<HTMLDivElement>(null);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [canScrollLeft, setCanScrollLeft] = useState(false);
  const [canScrollRight, setCanScrollRight] = useState(true);

  const stories: StoryCard[] = [
    {
      id: '01',
      tag: 'PENGLIHATAN AI',
      title: 'Membaca Gejala Daun Sebelum Menyebar.',
      description:
        'Kamera smartphone membaca pola lesi klorosis pada helai daun padi dan mengenali patogen Hawar Daun Bakteri secara dini langsung di pematang.',
      image: '/images/onboarding_1.jpeg',
      badgeLabel: 'Akurasi Model',
      badgeValue: 'Hawar Daun (94.7%)',
      badgeIcon: <Sprout className="w-3.5 h-3.5 text-[#16A34A]" />,
    },
    {
      id: '02',
      tag: 'AGRO-METEOROLOGI',
      title: 'Waktu Tepat di Tengah Ketidakpastian Langit.',
      description:
        'Menghitung jam optimal penyerapan pupuk daun antara 09.00 – 11.00 WIB sebelum angin kencang dan hujan lebat sore melarutkan nutrisi.',
      image: '/images/splash_background.jpeg',
      badgeLabel: 'Jendela Pemupukan',
      badgeValue: '09.00 – 11.00 WIB (Aman)',
      badgeIcon: <CloudSun className="w-3.5 h-3.5 text-amber-500" />,
    },
    {
      id: '03',
      tag: 'FASE PERTUMBUHAN',
      title: 'Memahami Umur Tanaman, Mengatur Nutrisi.',
      description:
        'Melacak ritme biologis tanaman padi hari demi hari (45 HST) untuk memastikan pembentukan anakan produktif maksimum sebelum malai keluar.',
      image: '/images/onboarding_3.jpeg',
      badgeLabel: 'Status Tanaman',
      badgeValue: '45 HST &bull; Fase Anakan',
      badgeIcon: <ShieldCheck className="w-3.5 h-3.5 text-emerald-500" />,
    },
    {
      id: '04',
      tag: 'JEJARING HAMPARAN',
      title: 'Radar Peringatan Risiko Radius 8 KM.',
      description:
        'Gotong royong deteksi antar petani tetangga memetakan ancaman jamur Blast dan wereng sebelum spora tertiup melintasi petak Anda.',
      image: '/images/onboarding_2.jpeg',
      badgeLabel: 'Kewaspadaan Sekitar',
      badgeValue: 'Radius 8 KM (3 Laporan)',
      badgeIcon: <Radio className="w-3.5 h-3.5 text-red-500" />,
    },
    {
      id: '05',
      tag: 'PENDAMPINGAN LAPANGAN',
      title: 'Keputusan Berbasis Validasi Penyuluh.',
      description:
        'Kecerdasan buatan mempercepat deteksi awal, verifikasi penyuluh (PPL) mengonfirmasi tindakan agar sesuai dengan kearifan tanah setempat.',
      image: '/images/hero_paddy.jpg',
      badgeLabel: 'Verifikasi Resmi',
      badgeValue: 'BPP Sindang Indramayu',
      badgeIcon: <UserCheck className="w-3.5 h-3.5 text-[#16A34A]" />,
    },
  ];

  const updateScrollState = () => {
    const el = scrollContainerRef.current;
    if (!el) return;

    setCanScrollLeft(el.scrollLeft > 20);
    setCanScrollRight(el.scrollLeft < el.scrollWidth - el.clientWidth - 20);

    // Calculate current visible slide index
    const slideWidth = el.querySelector('.story-slide')?.clientWidth || 1;
    const gap = 24;
    const index = Math.round(el.scrollLeft / (slideWidth + gap));
    setCurrentIndex(Math.min(stories.length - 1, Math.max(0, index)));
  };

  useEffect(() => {
    const el = scrollContainerRef.current;
    if (!el) return;

    el.addEventListener('scroll', updateScrollState, { passive: true });
    updateScrollState();

    return () => el.removeEventListener('scroll', updateScrollState);
  }, []);

  const scrollToIndex = (index: number) => {
    const el = scrollContainerRef.current;
    if (!el) return;

    const slides = el.querySelectorAll('.story-slide');
    if (slides[index]) {
      slides[index].scrollIntoView({
        behavior: 'smooth',
        block: 'nearest',
        inline: 'center',
      });
    }
  };

  const scrollPrev = () => {
    if (currentIndex > 0) {
      scrollToIndex(currentIndex - 1);
    }
  };

  const scrollNext = () => {
    if (currentIndex < stories.length - 1) {
      scrollToIndex(currentIndex + 1);
    }
  };

  return (
    <section
      id="cerita"
      className="relative w-full py-20 px-6 sm:px-12 md:px-20 bg-[#F8FAF8] text-gray-900 overflow-hidden border-t border-gray-200/80"
    >
      <div className="max-w-6xl mx-auto space-y-8">
        {/* Top Header & Navigation Buttons */}
        <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
          <div className="text-left space-y-2">
            <div className="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#16A34A]">
              Kisah dari Balik Hamparan Sawah
            </div>
            <h2 className="text-3xl sm:text-5xl font-black text-gray-950 tracking-tight leading-tight">
              Bagaimana P.A.D.I. Bekerja di Lapangan.
            </h2>
            <p className="text-sm sm:text-base text-gray-600 max-w-lg">
              Setiap inovasi dirancang untuk memperkuat keputusan petani di sawah, bukan menggantikan keahlian mereka.
            </p>
          </div>

          {/* Navigation Controls: Slide Counter & Prev/Next Arrow Buttons */}
          <div className="flex items-center gap-4 self-start sm:self-auto shrink-0">
            <span className="text-xs font-bold text-gray-500 bg-white px-3.5 py-2 rounded-full shadow-xs border border-gray-200">
              0{currentIndex + 1} / 0{stories.length}
            </span>

            <div className="flex items-center gap-2">
              <button
                onClick={scrollPrev}
                disabled={!canScrollLeft}
                aria-label="Slide sebelumnya"
                className={`w-10 h-10 rounded-full flex items-center justify-center border transition-all ${
                  canScrollLeft
                    ? 'bg-white border-gray-200 text-gray-800 hover:bg-gray-50 shadow-xs active:scale-95'
                    : 'bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed'
                }`}
              >
                <ArrowLeft className="w-4 h-4" />
              </button>

              <button
                onClick={scrollNext}
                disabled={!canScrollRight}
                aria-label="Slide berikutnya"
                className={`w-10 h-10 rounded-full flex items-center justify-center border transition-all ${
                  canScrollRight
                    ? 'bg-[#16A34A] border-[#16A34A] text-white hover:bg-[#15803D] shadow-sm active:scale-95'
                    : 'bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed'
                }`}
              >
                <ArrowRight className="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        {/* Horizontal Smooth Carousel Track (Native Snapping, Drag/Swipe, No Pinning Collision) */}
        <div
          ref={scrollContainerRef}
          className="flex items-stretch gap-6 overflow-x-auto pb-6 pt-2 scrollbar-none snap-x snap-mandatory scroll-smooth -mx-6 px-6 sm:-mx-12 sm:px-12 md:-mx-20 md:px-20"
          style={{ scrollbarWidth: 'none', msOverflowStyle: 'none' }}
        >
          {stories.map((story, idx) => (
            <div
              key={story.id}
              className="story-slide snap-center relative w-[300px] xs:w-[360px] sm:w-[480px] md:w-[540px] rounded-[32px] bg-white border border-gray-200/90 shadow-[0_12px_35px_rgba(0,0,0,0.06)] flex flex-col justify-between p-6 sm:p-8 shrink-0 overflow-hidden text-left transition-all duration-300 hover:shadow-xl"
            >
              {/* Top Tag & Floating Status Badge */}
              <div className="flex items-center justify-between gap-2 pb-4">
                <span className="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-50 text-[#16A34A] border border-emerald-100">
                  {story.tag}
                </span>

                <div className="bg-gray-50 px-3 py-1 rounded-full border border-gray-200/80 flex items-center gap-1.5 text-xs font-semibold text-gray-800 shrink-0">
                  {story.badgeIcon}
                  <span className="text-[11px]">{story.badgeValue}</span>
                </div>
              </div>

              {/* Photo Showcase */}
              <div className="relative w-full h-48 sm:h-56 rounded-2xl overflow-hidden shadow-inner my-2 bg-gray-100 border border-gray-100">
                <img
                  src={story.image}
                  alt={story.title}
                  className="w-full h-full object-cover filter contrast-[1.03]"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent" />
                <div className="absolute bottom-3 left-4 text-[11px] font-semibold text-white/95">
                  {story.badgeLabel}
                </div>
              </div>

              {/* Bottom Text Content */}
              <div className="space-y-2 pt-4">
                <h3 className="text-xl sm:text-2xl font-black text-gray-950 tracking-tight leading-snug">
                  {story.title}
                </h3>
                <p className="text-xs sm:text-sm text-gray-600 leading-relaxed font-normal">
                  {story.description}
                </p>
              </div>
            </div>
          ))}
        </div>

        {/* Bottom Pagination Dots */}
        <div className="flex items-center justify-center gap-2 pt-2">
          {stories.map((_, i) => (
            <button
              key={i}
              onClick={() => scrollToIndex(i)}
              aria-label={`Buka slide ${i + 1}`}
              className={`h-2 rounded-full transition-all duration-300 ${
                i === currentIndex ? 'w-8 bg-[#16A34A]' : 'w-2 bg-gray-300 hover:bg-gray-400'
              }`}
            />
          ))}
        </div>
      </div>
    </section>
  );
};

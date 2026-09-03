import React from 'react';
import { useLenis } from './hooks/useLenis';
import { LightNavbar } from './components/navigation/LightNavbar';
import { HeroLightSection } from './components/light/HeroLightSection';
import { FeatureGreenSection } from './components/light/FeatureGreenSection';
import { AssistantSection } from './components/light/AssistantSection';
import { DownloadLightSection } from './components/light/DownloadLightSection';
import { LightFooter } from './components/light/LightFooter';
import CurvedLoop from './CurvedLoop';
import DriftWall from './DriftWall';
import { Eye, MousePointer, Sparkles } from 'lucide-react';

const driftWallItems = [
  {
    image: '/images/hero_paddy.jpg',
    title: 'Terasering Sawah Indramayu',
    href: '#beranda',
  },
  {
    image: 'https://images.unsplash.com/photo-1530053969600-caed2596d242?w=700&auto=format&fit=crop&q=80',
    title: 'Hamparan Hijau Subak',
    href: '#fitur',
  },
  {
    image: '/images/onboarding_1.jpeg',
    title: 'Inspeksi Makro Daun Padi',
    href: '#fitur',
  },
  {
    image: 'https://images.unsplash.com/photo-1592982537447-7440770cbfc9?w=700&auto=format&fit=crop&q=80',
    title: 'Aktivitas Petani di Pematang',
    href: '#penyuluh',
  },
  {
    image: '/images/onboarding_2.jpeg',
    title: 'Penggunaan Aplikasi P.A.D.I.',
    href: '#wawasan',
  },
  {
    image: 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=700&auto=format&fit=crop&q=80',
    title: 'Bulir Padi Siap Panen',
    href: '#beranda',
  },
  {
    image: '/images/onboarding_3.jpeg',
    title: 'Fase Tanam Anakan (45 HST)',
    href: '#wawasan',
  },
  {
    image: 'https://images.unsplash.com/photo-1574943320219-553eb213f72d?w=700&auto=format&fit=crop&q=80',
    title: 'Embun Pagi Helai Daun',
    href: '#fitur',
  },
  {
    image: '/images/splash_background.jpeg',
    title: 'Kabut Fajar di Pedesaan',
    href: '#beranda',
  },
  {
    image: 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=700&auto=format&fit=crop&q=80',
    title: 'Persemaian Bibit Muda',
    href: '#fitur',
  },
  {
    image: 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff?w=700&auto=format&fit=crop&q=80',
    title: 'Langit Cerah & Pemupukan',
    href: '#wawasan',
  },
  {
    image: 'https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?w=700&auto=format&fit=crop&q=80',
    title: 'Irigasi Lahan Pertanian',
    href: '#penyuluh',
  },
];

export function App() {
  // Lenis smooth scroll
  useLenis();

  return (
    <div className="min-h-screen bg-[#F8FAF8] text-gray-900 flex flex-col selection:bg-[#DCFCE7] selection:text-[#15803D]">
      {/* 1. Clean Floating Pill Navbar */}
      <LightNavbar />

      {/* 2. Seamless Natural Narrative Flow matching Dribbble Reference */}
      <main className="flex-1 w-full flex flex-col">
        {/* Section 1: Hero Section with 3 Floating UI Cards */}
        <HeroLightSection />

        {/* Dynamic Curved Loop Ribbon Transition */}
        <div className="w-full my-2 sm:my-4 relative overflow-hidden">
          <CurvedLoop
            marqueeText="P.A.D.I. ✦ Predictive Agriculture ✦ Sawah Sehat ✦ Panen Melimpah ✦ Deteksi Cepat ✦"
            speed={2.2}
            curveAmount={140}
            direction="left"
            interactive
            className="fill-[#16A34A] text-lg sm:text-2xl font-black"
          />
        </div>

        {/* Section 2: Large Green Feature Showcase Card */}
        <FeatureGreenSection />

        {/* Section 3: Assistant Section ("Not Harder, Smarter" - Bar Chart + Dark Accordions) */}
        <AssistantSection />

        {/* Section 4: 3D Perspective DriftWall Visual Gallery */}
        <section className="relative w-full py-20 px-6 sm:px-12 md:px-20 bg-gradient-to-b from-[#F8FAF8] via-white to-[#F8FAF8] overflow-hidden text-center border-t border-gray-100">
          <div className="max-w-3xl mx-auto space-y-3 mb-10">
            <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 text-[#16A34A] border border-emerald-200/60 text-xs font-bold uppercase tracking-wider shadow-xs">
              <Sparkles className="w-3.5 h-3.5" />
              <span>Galeri Visual Dokumentasi 3D</span>
            </div>

            <h2 className="text-3xl sm:text-5xl font-black text-gray-950 tracking-tight leading-tight">
              Jendela Nyata Sawah & Tradisi Nusantara.
            </h2>

            <p className="text-sm sm:text-base text-gray-600 max-w-xl mx-auto leading-relaxed">
              Dinding interaktif berperspektif 3D yang merekam kehidupan pematang, pertumbuhan bulir padi, dan penerapan teknologi AI presisi di lapangan.
            </p>
          </div>

          {/* Elevated 3D Canvas Box */}
          <div
            style={{ height: 620 }}
            className="relative w-full max-w-6xl mx-auto rounded-[36px] overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.08)] border border-gray-200/80 bg-white"
          >
            <DriftWall
              items={driftWallItems}
              columns={5}
              tileWidth={230}
              tileHeight={152}
              gap={20}
              tilt={15}
              turn={-12}
              perspective={1200}
              depth={130}
              speed={38}
              direction="up"
              variance={0.4}
              parallax={0.65}
              lift={60}
              fade={0.5}
              dim={0.92}
              overlayColor="rgba(22, 163, 74, 0.04)"
              radius={16}
              roll={0}
              pauseOnHover={true}
              grayscale={false}
            />

            {/* Floating Interaction Hint Banner */}
            <div className="absolute bottom-5 inset-x-6 flex flex-col sm:flex-row items-center justify-between gap-3 pointer-events-none">
              <div className="bg-white/90 backdrop-blur-md px-4 py-2 rounded-full shadow-md border border-gray-100 flex items-center gap-2 text-xs font-semibold text-gray-700">
                <MousePointer className="w-3.5 h-3.5 text-[#16A34A]" />
                <span>Gerakkan kursor untuk memiringkan 3D &bull; Arahkan foto untuk mengangkat tile</span>
              </div>

              <div className="bg-emerald-50/95 backdrop-blur-md px-4 py-2 rounded-full shadow-md border border-emerald-200/80 flex items-center gap-2 text-xs font-bold text-[#16A34A]">
                <Eye className="w-3.5 h-3.5" />
                <span>12 Foto Dokumentasi Lapangan</span>
              </div>
            </div>
          </div>
        </section>

        {/* Section 5: Clean Download Conversion CTA */}
        <DownloadLightSection />
      </main>

      {/* 3. Clean Light Footer */}
      <LightFooter />
    </div>
  );
}

export default App;

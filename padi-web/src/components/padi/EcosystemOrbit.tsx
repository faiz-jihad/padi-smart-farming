import React from 'react';
import {
  Scan,
  CloudSun,
  Radio,
  ShieldCheck,
  Sprout,
  BookOpen,
  ShoppingBag,
  ListTodo,
} from 'lucide-react';

export const EcosystemOrbit: React.FC = () => {
  const nodes = [
    { title: 'AI Plant Check', icon: Scan, color: '#41A55B', angle: 0 },
    { title: 'Weather Intelligence', icon: CloudSun, color: '#38BDF8', angle: 45 },
    { title: 'Disease Radar', icon: Radio, color: '#F87171', angle: 90 },
    { title: 'PPL Validation', icon: ShieldCheck, color: '#F5C842', angle: 135 },
    { title: 'Crop Cycle (HST)', icon: Sprout, color: '#34D399', angle: 180 },
    { title: 'Farm Record', icon: BookOpen, color: '#A78BFA', angle: 225 },
    { title: 'Marketplace', icon: ShoppingBag, color: '#FB923C', angle: 270 },
    { title: 'Farm Priority', icon: ListTodo, color: '#FBBF24', angle: 315 },
  ];

  return (
    <div className="relative w-[340px] sm:w-[420px] md:w-[500px] h-[340px] sm:h-[420px] md:h-[500px] mx-auto flex items-center justify-center select-none">
      {/* Orbital Circles */}
      <div className="absolute inset-4 rounded-full border border-[#41A55B]/15 animate-spin [animation-duration:90s]" />
      <div className="absolute inset-16 rounded-full border border-dashed border-[#41A55B]/25 animate-spin [animation-duration:60s] [animation-direction:reverse]" />
      <div className="absolute inset-28 rounded-full border border-[#41A55B]/20" />

      {/* Center Core: P.A.D.I. Logo & Intelligence Kernel */}
      <div className="relative z-20 w-24 sm:w-28 h-24 sm:h-28 rounded-full bg-gradient-to-tr from-[#063D2B] via-[#075B3B] to-[#0C7047] border-2 border-[#F5C842] shadow-[0_0_35px_rgba(245,200,66,0.3)] flex flex-col items-center justify-center text-center p-2">
        <div className="text-[10px] tracking-widest uppercase font-bold text-[#F5C842]">Platform</div>
        <div className="text-xl sm:text-2xl font-black text-white tracking-tight">P.A.D.I.</div>
        <div className="text-[8px] text-white/60 font-medium">Core Intelligence</div>
      </div>

      {/* Orbiting Pillar Nodes */}
      {nodes.map((node) => {
        const rad = (node.angle * Math.PI) / 180;
        // Radius responsive: ~130px on mobile, ~185px on desktop
        return (
          <div
            key={node.title}
            className="absolute z-20 flex flex-col items-center group cursor-pointer transition-transform hover:scale-110"
            style={{
              transform: `translate(${Math.cos(rad) * 155}px, ${Math.sin(rad) * 155}px)`,
            }}
          >
            <div
              className="w-10 sm:w-11 h-10 sm:h-11 rounded-2xl bg-[#0A1F16] border border-white/20 shadow-lg flex items-center justify-center transition-all group-hover:border-[#F5C842]"
              style={{ color: node.color }}
            >
              <node.icon className="w-5 h-5" />
            </div>
            <span className="mt-1 text-[10px] sm:text-[11px] font-bold text-white/80 bg-black/60 px-2 py-0.5 rounded-full backdrop-blur-sm whitespace-nowrap shadow border border-white/5">
              {node.title}
            </span>
          </div>
        );
      })}
    </div>
  );
};

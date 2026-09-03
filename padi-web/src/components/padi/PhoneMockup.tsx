import React from 'react';

interface PhoneMockupProps {
  children: React.ReactNode;
  className?: string;
  glow?: boolean;
}

export const PhoneMockup: React.FC<PhoneMockupProps> = ({
  children,
  className = '',
  glow = true,
}) => {
  return (
    <div className={`relative mx-auto select-none ${className}`}>
      {/* Ambient Phone Shadow & Green Glow */}
      {glow && (
        <div
          className="absolute -inset-4 md:-inset-8 bg-gradient-to-tr from-[#0C7047]/30 via-[#41A55B]/15 to-transparent rounded-[50px] blur-2xl pointer-events-none"
          aria-hidden="true"
        />
      )}

      {/* Phone Outer Chassis (Apple/Titanium Dark Forest Finish) */}
      <div className="relative w-[300px] sm:w-[330px] md:w-[360px] h-[610px] sm:h-[660px] md:h-[720px] bg-[#0A1F16] rounded-[48px] md:rounded-[54px] p-3 md:p-3.5 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.8),0_0_0_1px_rgba(255,255,255,0.15),inset_0_1px_2px_rgba(255,255,255,0.25)] border-4 border-[#163628]">
        {/* Dynamic Island / Speaker Pill */}
        <div className="absolute top-6 left-1/2 -translate-x-1/2 w-28 md:w-32 h-6 bg-[#04120D] rounded-full z-30 flex items-center justify-between px-3 shadow-inner">
          <div className="w-2.5 h-2.5 rounded-full bg-[#0C7047]/60 flex items-center justify-center">
            <div className="w-1 h-1 rounded-full bg-[#41A55B]" />
          </div>
          <div className="w-2.5 h-2.5 rounded-full bg-[#1A382A]" />
        </div>

        {/* Side Hardware Buttons */}
        <div className="absolute -left-[6px] top-28 w-1 h-8 bg-[#1B3E2E] rounded-l-md" />
        <div className="absolute -left-[6px] top-40 w-1 h-12 bg-[#1B3E2E] rounded-l-md" />
        <div className="absolute -left-[6px] top-56 w-1 h-12 bg-[#1B3E2E] rounded-l-md" />
        <div className="absolute -right-[6px] top-36 w-1 h-16 bg-[#1B3E2E] rounded-r-md" />

        {/* Phone Screen Inner Bezel */}
        <div className="relative w-full h-full bg-[#0E1712] rounded-[38px] md:rounded-[44px] overflow-hidden flex flex-col border border-white/5">
          {/* Status Bar */}
          <div className="h-11 w-full flex items-center justify-between px-6 pt-1 text-[11px] font-semibold text-white/70 z-20 select-none">
            <span>09:41</span>
            <div className="flex items-center gap-1.5">
              <span className="text-[10px]">5G</span>
              <div className="w-4 h-2.5 border border-white/60 rounded-sm p-[1px] flex items-center">
                <div className="h-full w-3/4 bg-white/80 rounded-2xs" />
              </div>
            </div>
          </div>

          {/* Screen Dynamic Inner Viewport */}
          <div className="flex-1 overflow-y-auto overflow-x-hidden relative text-white">
            {children}
          </div>

          {/* Home Indicator Bar */}
          <div className="h-5 w-full flex items-center justify-center pb-1 z-20">
            <div className="w-32 h-1 bg-white/30 rounded-full" />
          </div>
        </div>
      </div>
    </div>
  );
};

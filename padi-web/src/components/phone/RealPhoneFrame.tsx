import React from 'react';

interface RealPhoneFrameProps {
  children: React.ReactNode;
  className?: string;
}

export const RealPhoneFrame: React.FC<RealPhoneFrameProps> = ({
  children,
  className = '',
}) => {
  return (
    <div className={`relative mx-auto select-none ${className}`}>
      {/* Phone Hardware Chassis */}
      <div className="relative w-[285px] xs:w-[310px] sm:w-[335px] h-[590px] xs:h-[630px] sm:h-[680px] bg-[#121614] rounded-[44px] p-3 shadow-[0_20px_50px_-10px_rgba(0,0,0,0.6),0_0_0_1px_rgba(255,255,255,0.12)] border border-white/10">
        {/* Subtle camera punch / dynamic island */}
        <div className="absolute top-5 left-1/2 -translate-x-1/2 w-24 h-5 bg-[#050706] rounded-full z-30 flex items-center justify-center">
          <div className="w-2.5 h-2.5 rounded-full bg-[#17221C]" />
        </div>

        {/* Screen Viewport */}
        <div className="relative w-full h-full bg-[#0D1410] rounded-[36px] overflow-hidden flex flex-col border border-white/5 text-white">
          {/* Status Bar */}
          <div className="h-10 w-full flex items-center justify-between px-6 pt-1 text-[11px] font-semibold text-white/60 z-20">
            <span>09:41</span>
            <div className="flex items-center gap-1.5">
              <span className="text-[10px]">LTE</span>
              <div className="w-4 h-2.5 border border-white/50 rounded-xs p-[1px] flex items-center">
                <div className="h-full w-3/4 bg-white/70 rounded-2xs" />
              </div>
            </div>
          </div>

          {/* Internal Screen Content */}
          <div className="flex-1 overflow-y-auto overflow-x-hidden relative">
            {children}
          </div>

          {/* Home Bar */}
          <div className="h-4 w-full flex items-center justify-center pb-1 z-20">
            <div className="w-28 h-1 bg-white/20 rounded-full" />
          </div>
        </div>
      </div>
    </div>
  );
};

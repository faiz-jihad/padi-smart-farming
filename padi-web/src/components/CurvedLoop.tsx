import React, { useRef, useEffect, useState, useMemo, useId, FC, PointerEvent } from 'react';

export interface CurvedLoopProps {
  marqueeText?: string;
  speed?: number;
  className?: string;
  curveAmount?: number;
  direction?: 'left' | 'right';
  interactive?: boolean;
}

export const CurvedLoop: FC<CurvedLoopProps> = ({
  marqueeText = 'P.A.D.I. ✦ Predictive Agriculture & Disease Intelligence ✦ Sawah Sehat Panen Melimpah ✦',
  speed = 2,
  className = '',
  curveAmount = 250,
  direction = 'left',
  interactive = true,
}) => {
  const text = useMemo(() => {
    const hasTrailing = /\s|\u00A0$/.test(marqueeText);
    return (hasTrailing ? marqueeText.replace(/\s+$/, '') : marqueeText) + '\u00A0\u00A0\u00A0';
  }, [marqueeText]);

  const measureRef = useRef<SVGTextElement | null>(null);
  const textPathRef = useRef<SVGTextPathElement | null>(null);
  const [spacing, setSpacing] = useState(0);
  const [offset, setOffset] = useState(0);
  const uid = useId().replace(/:/g, '');
  const pathId = `curve-${uid}`;

  // SVG dimensions
  const svgWidth = 1440;
  const startY = 60;
  const pathD = `M-200,${startY} Q${svgWidth / 2},${startY + curveAmount} ${svgWidth + 200},${startY}`;

  const dragRef = useRef(false);
  const lastXRef = useRef(0);
  const dirRef = useRef<'left' | 'right'>(direction);
  const velRef = useRef(0);

  const textLength = spacing;
  const totalText = textLength
    ? Array(Math.max(3, Math.ceil((svgWidth + 600) / textLength) + 2))
        .fill(text)
        .join('')
    : text;

  useEffect(() => {
    if (measureRef.current) {
      try {
        const length = measureRef.current.getComputedTextLength();
        if (length > 0) setSpacing(length);
      } catch (e) {
        setSpacing(350);
      }
    }
  }, [text, className]);

  // Animation Loop with drag momentum
  useEffect(() => {
    let animId: number;

    const loop = () => {
      if (!dragRef.current) {
        // Friction decay for drag momentum
        velRef.current *= 0.95;
        if (Math.abs(velRef.current) < 0.05) velRef.current = 0;

        const effectiveSpeed = (dirRef.current === 'left' ? -1 : 1) * speed + velRef.current;

        setOffset((prev) => {
          if (!spacing) return prev;
          let next = prev + effectiveSpeed;
          // Wrap around seamlessly
          if (next <= -spacing) next += spacing;
          if (next >= spacing) next -= spacing;
          return next;
        });
      }

      animId = requestAnimationFrame(loop);
    };

    animId = requestAnimationFrame(loop);
    return () => cancelAnimationFrame(animId);
  }, [spacing, speed]);

  // Pointer drag interaction
  const handlePointerDown = (e: PointerEvent<SVGSVGElement>) => {
    if (!interactive) return;
    dragRef.current = true;
    lastXRef.current = e.clientX;
    velRef.current = 0;
  };

  const handlePointerMove = (e: PointerEvent<SVGSVGElement>) => {
    if (!interactive || !dragRef.current) return;
    const delta = e.clientX - lastXRef.current;
    lastXRef.current = e.clientX;
    velRef.current = delta * 0.8;

    if (delta !== 0) {
      dirRef.current = delta > 0 ? 'right' : 'left';
    }

    setOffset((prev) => {
      if (!spacing) return prev;
      let next = prev + delta;
      if (next <= -spacing) next += spacing;
      if (next >= spacing) next -= spacing;
      return next;
    });
  };

  const handlePointerUp = () => {
    if (!interactive) return;
    dragRef.current = false;
  };

  return (
    <div className="relative w-full overflow-hidden select-none py-4 sm:py-6">
      <svg
        viewBox={`0 0 ${svgWidth} ${Math.max(140, startY + curveAmount + 60)}`}
        className={`w-full h-auto overflow-visible ${interactive ? 'cursor-grab active:cursor-grabbing' : ''}`}
        onPointerDown={handlePointerDown}
        onPointerMove={handlePointerMove}
        onPointerUp={handlePointerUp}
        onPointerCancel={handlePointerUp}
      >
        <defs>
          <path id={pathId} d={pathD} fill="none" />
        </defs>

        {/* Hidden measurement node */}
        <text
          ref={measureRef}
          className={`opacity-0 pointer-events-none font-extrabold uppercase tracking-wider text-xl sm:text-2xl ${className}`}
          x="-9999"
          y="-9999"
        >
          {text}
        </text>

        {/* Curved animated marquee path */}
        <text
          className={`font-black uppercase tracking-widest text-xl sm:text-2xl fill-[#16A34A] drop-shadow-xs ${className}`}
        >
          <textPath
            ref={textPathRef}
            href={`#${pathId}`}
            startOffset={`${offset}px`}
          >
            {totalText}
          </textPath>
        </text>
      </svg>
    </div>
  );
};

export default CurvedLoop;

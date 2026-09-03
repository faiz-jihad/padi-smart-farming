import React, { useRef, useEffect } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

interface StickyStoryProps {
  children: (progress: number) => React.ReactNode;
  heightViewport?: number; // e.g., 2.5 means 250vh scroll duration
  className?: string;
  onProgress?: (progress: number) => void;
}

export const StickyStory: React.FC<StickyStoryProps> = ({
  children,
  heightViewport = 2.5,
  className = '',
  onProgress,
}) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const stickyRef = useRef<HTMLDivElement>(null);
  const [progress, setProgress] = React.useState(0);

  useEffect(() => {
    const container = containerRef.current;
    const sticky = stickyRef.current;
    if (!container || !sticky) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      setProgress(1);
      return;
    }

    const ctx = gsap.context(() => {
      ScrollTrigger.create({
        trigger: container,
        start: 'top top',
        end: 'bottom bottom',
        pin: sticky,
        pinSpacing: false,
        scrub: 0.5,
        onUpdate: (self) => {
          const p = self.progress;
          setProgress(p);
          if (onProgress) onProgress(p);
        },
      });
    }, containerRef);

    return () => ctx.revert();
  }, [onProgress]);

  return (
    <div
      ref={containerRef}
      style={{ height: `${heightViewport * 100}vh` }}
      className={`relative w-full ${className}`}
    >
      <div
        ref={stickyRef}
        className="sticky top-0 left-0 w-full h-screen overflow-hidden flex flex-col items-center justify-center"
      >
        {children(progress)}
      </div>
    </div>
  );
};

import React from 'react';

interface SectionTransitionProps {
  fromColor?: string;
  toColor?: string;
  className?: string;
}

export const SectionTransition: React.FC<SectionTransitionProps> = ({
  fromColor = 'transparent',
  toColor = '#141A17',
  className = '',
}) => {
  return (
    <div
      style={{
        background: `linear-gradient(to bottom, ${fromColor}, ${toColor})`,
      }}
      className={`w-full h-24 md:h-36 pointer-events-none ${className}`}
      aria-hidden="true"
    />
  );
};

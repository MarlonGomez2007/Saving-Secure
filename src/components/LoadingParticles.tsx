import React, { useEffect, useRef } from 'react';

const LoadingParticles = () => {
  const canvasRef = useRef<HTMLCanvasElement>(null);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Set canvas size
    const setCanvasSize = () => {
      canvas.width = canvas.offsetWidth;
      canvas.height = canvas.offsetHeight;
    };
    setCanvasSize();
    window.addEventListener('resize', setCanvasSize);

    // Particle class with enhanced properties
    class Particle {
      x: number;
      y: number;
      size: number;
      speedX: number;
      speedY: number;
      opacity: number;
      pulse: number;
      pulseSpeed: number;

      constructor() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.size = Math.random() * 5 + 3; // Slightly larger particles
        this.speedX = Math.random() * 0.8 - 0.4; // Faster movement
        this.speedY = Math.random() * 0.8 - 0.4;
        this.opacity = Math.random() * 0.5 + 0.4; // Higher base opacity
        this.pulse = 0;
        this.pulseSpeed = Math.random() * 0.04 + 0.02;
      }

      update() {
        this.x += this.speedX;
        this.y += this.speedY;

        // Wrap around edges with smooth transition
        if (this.x > canvas.width) this.x = 0;
        if (this.x < 0) this.x = canvas.width;
        if (this.y > canvas.height) this.y = 0;
        if (this.y < 0) this.y = canvas.height;

        // Pulsing effect
        this.pulse += this.pulseSpeed;
        const pulseFactor = Math.sin(this.pulse) * 0.2 + 0.8;
        this.size = (Math.random() * 5 + 3) * pulseFactor;
      }

      draw() {
        if (!ctx) return;
        
        // Create gradient for each particle
        const gradient = ctx.createRadialGradient(
          this.x, this.y, 0,
          this.x, this.y, this.size
        );
        gradient.addColorStop(0, `rgba(254, 205, 2, ${this.opacity})`);
        gradient.addColorStop(1, 'rgba(254, 205, 2, 0)');

        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fillStyle = gradient;
        ctx.fill();
      }
    }

    // Create more particles for loading screen
    const particles: Particle[] = [];
    const particleCount = 75; // More particles for richer effect
    for (let i = 0; i < particleCount; i++) {
      particles.push(new Particle());
    }

    // Animation with enhanced connection effect
    let animationFrameId: number;
    const animate = () => {
      if (!ctx) return;
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      particles.forEach(particle => {
        particle.update();
        particle.draw();
      });

      // Enhanced connection effect
      particles.forEach(a => {
        particles.forEach(b => {
          const dx = a.x - b.x;
          const dy = a.y - b.y;
          const distance = Math.sqrt(dx * dx + dy * dy);

          if (distance < 150) { // Increased connection distance
            ctx.beginPath();
            const opacity = 0.3 * (1 - distance / 150); // Higher base opacity
            ctx.strokeStyle = `rgba(254, 205, 2, ${opacity})`;
            ctx.lineWidth = 2 * (1 - distance / 150); // Thicker lines that fade with distance
            ctx.moveTo(a.x, a.y);
            ctx.lineTo(b.x, b.y);
            ctx.stroke();
          }
        });
      });

      animationFrameId = requestAnimationFrame(animate);
    };

    animate();

    return () => {
      window.removeEventListener('resize', setCanvasSize);
      cancelAnimationFrame(animationFrameId);
    };
  }, []);

  return (
    <canvas
      ref={canvasRef}
      className="absolute inset-0 w-full h-full pointer-events-none"
      style={{ opacity: 1, zIndex: 1 }}
    />
  );
};

export default LoadingParticles; 
<template>
  <div class="animated-bg-wrapper">
    <canvas ref="canvas" id="canvas"></canvas>
    <div class="login-card">
      <slot name="login-form"></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const canvas = ref(null);
let ctx = null;
let animationId = null;

const particles = [];
const fireworkParticles = [];
const dustParticles = [];
const ripples = [];
const techRipples = [];

const mouse = (() => {
  let state = { x: null, y: null };
  return {
    get x() {
      return state.x;
    },
    get y() {
      return state.y;
    },
    set({ x, y }) {
      state = { x, y };
    },
    reset() {
      state = { x: null, y: null };
    }
  };
})();

let backgroundHue = 0;
let frameCount = 0;
let autoDrift = true;

function adjustParticleCount() {
  const c = canvas.value;
  const particleConfig = {
    heightConditions: [200, 300, 400, 500, 600],
    widthConditions: [450, 600, 900, 1200, 1600],
    particlesForHeight: [40, 60, 70, 90, 110],
    particlesForWidth: [40, 50, 70, 90, 110]
  };

  let numParticles = 130;

  for (let i = 0; i < particleConfig.heightConditions.length; i++) {
    if (c.height < particleConfig.heightConditions[i]) {
      numParticles = particleConfig.particlesForHeight[i];
      break;
    }
  }

  for (let i = 0; i < particleConfig.widthConditions.length; i++) {
    if (c.width < particleConfig.widthConditions[i]) {
      numParticles = Math.min(
        numParticles,
        particleConfig.particlesForWidth[i]
      );
      break;
    }
  }

  return numParticles;
}

class Particle {
  constructor(x, y, isFirework = false) {
    const baseSpeed = isFirework
      ? Math.random() * 2 + 1
      : Math.random() * 0.5 + 0.3;

    Object.assign(this, {
      isFirework,
      x,
      y,
      vx: Math.cos(Math.random() * Math.PI * 2) * baseSpeed,
      vy: Math.sin(Math.random() * Math.PI * 2) * baseSpeed,
      size: isFirework ? Math.random() * 2 + 2 : Math.random() * 3 + 1,
      hue: Math.random() * 360,
      alpha: 1,
      sizeDirection: Math.random() < 0.5 ? -1 : 1,
      trail: []
    });
  }

  update(mouse) {
    const c = canvas.value;
    const dist =
      mouse.x !== null ? (mouse.x - this.x) ** 2 + (mouse.y - this.y) ** 2 : 0;

    if (!this.isFirework) {
      const force = dist && dist < 22500 ? (22500 - dist) / 22500 : 0;

      if (mouse.x === null && autoDrift) {
        this.vx += (Math.random() - 0.5) * 0.03;
        this.vy += (Math.random() - 0.5) * 0.03;
      }

      if (dist) {
        const sqrtDist = Math.sqrt(dist);
        this.vx += ((mouse.x - this.x) / sqrtDist) * force * 0.1;
        this.vy += ((mouse.y - this.y) / sqrtDist) * force * 0.1;
      }

      this.vx *= mouse.x !== null ? 0.99 : 0.998;
      this.vy *= mouse.y !== null ? 0.99 : 0.998;
    } else {
      this.alpha -= 0.02;
    }

    this.x += this.vx;
    this.y += this.vy;

    if (this.x <= 0 || this.x >= canvas.value.width - 1) this.vx *= -0.9;
    if (this.y < 0 || this.y > canvas.value.height) this.vy *= -0.9;

    this.size += this.sizeDirection * 0.1;
    if (this.size > 4 || this.size < 1) this.sizeDirection *= -1;

    this.hue = (this.hue + 0.3) % 360;

    if (
      frameCount % 2 === 0 &&
      (Math.abs(this.vx) > 0.1 || Math.abs(this.vy) > 0.1)
    ) {
      this.trail.push({
        x: this.x,
        y: this.y,
        hue: this.hue,
        alpha: this.alpha
      });
      if (this.trail.length > 15) this.trail.shift();
    }
  }

  draw(ctx) {
    const gradient = ctx.createRadialGradient(
      this.x,
      this.y,
      0,
      this.x,
      this.y,
      this.size
    );
    gradient.addColorStop(
      0,
      `hsla(${this.hue}, 80%, 60%, ${Math.max(this.alpha, 0)})`
    );
    gradient.addColorStop(
      1,
      `hsla(${this.hue + 30}, 80%, 30%, ${Math.max(this.alpha, 0)})`
    );

    ctx.fillStyle = gradient;
    ctx.shadowBlur = canvas.value.width > 900 ? 10 : 0;
    ctx.shadowColor = `hsl(${this.hue}, 80%, 60%)`;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
    ctx.fill();
    ctx.shadowBlur = 0;

    if (this.trail.length > 1) {
      ctx.beginPath();
      ctx.lineWidth = 1.5;
      for (let i = 0; i < this.trail.length - 1; i++) {
        const { x: x1, y: y1, hue: h1, alpha: a1 } = this.trail[i];
        const { x: x2, y: y2 } = this.trail[i + 1];
        ctx.strokeStyle = `hsla(${h1}, 80%, 60%, ${Math.max(a1, 0)})`;
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
      }
      ctx.stroke();
    }
  }

  isDead() {
    return this.isFirework && this.alpha <= 0;
  }
}

class DustParticle {
  constructor() {
    Object.assign(this, {
      x: Math.random() * canvas.value.width,
      y: Math.random() * canvas.value.height,
      size: Math.random() * 1.5 + 0.5,
      hue: Math.random() * 360,
      vx: (Math.random() - 0.5) * 0.05,
      vy: (Math.random() - 0.5) * 0.05
    });
  }

  update() {
    this.x = (this.x + this.vx + canvas.value.width) % canvas.value.width;
    this.y = (this.y + this.vy + canvas.value.height) % canvas.value.height;
    this.hue = (this.hue + 0.1) % 360;
  }

  draw(ctx) {
    ctx.fillStyle = `hsla(${this.hue}, 30%, 70%, 0.3)`;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
    ctx.fill();
  }
}

class Ripple {
  constructor(x, y, hue = 0, maxRadius = 30) {
    Object.assign(this, { x, y, radius: 0, maxRadius, alpha: 0.5, hue });
  }

  update() {
    this.radius += 1.5;
    this.alpha -= 0.01;
    this.hue = (this.hue + 5) % 360;
  }

  draw(ctx) {
    ctx.strokeStyle = `hsla(${this.hue}, 80%, 60%, ${this.alpha})`;
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
    ctx.stroke();
  }

  isDone() {
    return this.alpha <= 0;
  }
}

function createParticles() {
  particles.length = 0;
  dustParticles.length = 0;

  const numParticles = adjustParticleCount();
  for (let i = 0; i < numParticles; i++) {
    particles.push(
      new Particle(Math.random() * canvas.value.width, Math.random() * canvas.value.height)
    );
  }
  for (let i = 0; i < 200; i++) {
    dustParticles.push(new DustParticle());
  }
}

function resizeCanvas() {
  const c = canvas.value;
  c.width = window.innerWidth;
  c.height = window.innerHeight;
  createParticles();
}

function drawBackground() {
  const c = canvas.value;
  backgroundHue = (backgroundHue + 0.2) % 360;
  const gradient = ctx.createLinearGradient(0, 0, 0, c.height);
  gradient.addColorStop(0, `hsl(${backgroundHue}, 40%, 15%)`);
  gradient.addColorStop(1, `hsl(${(backgroundHue + 120) % 360}, 40%, 25%)`);
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, c.width, c.height);
}

function connectParticles() {
  const gridSize = 120;
  const grid = new Map();

  particles.forEach((p) => {
    const key = `${Math.floor(p.x / gridSize)},${Math.floor(p.y / gridSize)}`;
    if (!grid.has(key)) grid.set(key, []);
    grid.get(key).push(p);
  });

  ctx.lineWidth = 1.5;
  particles.forEach((p) => {
    const gridX = Math.floor(p.x / gridSize);
    const gridY = Math.floor(p.y / gridSize);

    for (let dx = -1; dx <= 1; dx++) {
      for (let dy = -1; dy <= 1; dy++) {
        const key = `${gridX + dx},${gridY + dy}`;
        if (grid.has(key)) {
          grid.get(key).forEach((neighbor) => {
            if (neighbor !== p) {
              const diffX = neighbor.x - p.x;
              const diffY = neighbor.y - p.y;
              const dist = diffX * diffX + diffY * diffY;
              if (dist < 10000) {
                ctx.strokeStyle = `hsla(${(p.hue + neighbor.hue) / 2}, 80%, 60%, ${1 - Math.sqrt(dist) / 100})`;
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
                ctx.lineTo(neighbor.x, neighbor.y);
                ctx.stroke();
              }
            }
          });
        }
      }
    }
  });
}

function animate() {
  drawBackground();

  [dustParticles, particles, ripples, techRipples, fireworkParticles].forEach(
    (arr) => {
      for (let i = arr.length - 1; i >= 0; i--) {
        const obj = arr[i];
        obj.update(mouse);
        obj.draw(ctx);
        if (obj.isDone?.() || obj.isDead?.()) arr.splice(i, 1);
      }
    }
  );

  connectParticles();
  frameCount++;
  animationId = requestAnimationFrame(animate);
}

function handleMouseMove(e) {
  const rect = canvas.value.getBoundingClientRect();
  mouse.set({ x: e.clientX - rect.left, y: e.clientY - rect.top });
  techRipples.push(new Ripple(mouse.x, mouse.y));
  autoDrift = false;
}

function handleMouseLeave() {
  mouse.reset();
  autoDrift = true;
}

function handleClick(e) {
  const rect = canvas.value.getBoundingClientRect();
  const clickX = e.clientX - rect.left;
  const clickY = e.clientY - rect.top;

  ripples.push(new Ripple(clickX, clickY, 0, 60));

  for (let i = 0; i < 15; i++) {
    const angle = Math.random() * Math.PI * 2;
    const speed = Math.random() * 2 + 1;
    const particle = new Particle(clickX, clickY, true);
    particle.vx = Math.cos(angle) * speed;
    particle.vy = Math.sin(angle) * speed;
    fireworkParticles.push(particle);
  }
}

onMounted(() => {
  ctx = canvas.value.getContext('2d');
  resizeCanvas();
  animate();

  // Listen on window so the background receives pointer data
  window.addEventListener('mousemove', handleMouseMove);
  window.addEventListener('mouseout', handleMouseLeave);
  window.addEventListener('click', handleClick);
  window.addEventListener('resize', resizeCanvas);
});

onBeforeUnmount(() => {
  cancelAnimationFrame(animationId);
  // remove window listeners
  window.removeEventListener('mousemove', handleMouseMove);
  window.removeEventListener('mouseout', handleMouseLeave);
  window.removeEventListener('click', handleClick);
  window.removeEventListener('resize', resizeCanvas);
});
</script>

<style scoped>
html, body {
  margin: 0;
  padding: 0;
  overflow: hidden;
}
canvas {
  display: block;
}
.animated-bg-wrapper {
  position: fixed;
  inset: 0;
  width: 100%;
  height: 100vh;
  overflow: hidden;
  z-index: 0;
}
.animated-bg-wrapper canvas {
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  display: block;
  pointer-events: auto;
}
.login-card {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  z-index: 20;
  pointer-events: none; /* don't block canvas for demo slot; real login sits elsewhere */
}
</style>

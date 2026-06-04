/* =============================================================================
   CloudCush — diaper-three.js  v4 — Production Premium Edition
   REBUILT with ExtrudeGeometry body — true flat diaper silhouette
   No more LatheGeometry. Proper hourglass pad shape.
   ============================================================================= */

window.CCDiaperThree = (() => {
  'use strict';

  let renderer, scene, camera, diaperGroup, particleSystem, animFrameId;
  let isVisible = false, isInitialized = false;

  // Smooth lerp targets
  let targetRotY = 0, targetRotX = 0.05;
  let currentRotY = 0, currentRotX = 0.05;
  let floatTime = 0;
  let isDragging = false, prevMouseX = 0, prevMouseY = 0;
  let canvas;

  // Camera smooth follow
  let cameraTargetY = 0.04, cameraCurrY = 0.04;
  let cameraTargetZ = 5.6,  cameraCurrZ = 5.6;

  // ─── Diaper dimensions (world units) ──────────────────────────────────────
  const HH = 0.82;   // half-height  (total height: 1.64)
  const WT = 0.82;   // half-width at top / back waist  (widest)
  const WB = 0.72;   // half-width at bottom / front panel
  const WM = 0.40;   // half-width at crotch  (narrowest)
  const MY = -0.10;  // Y position of crotch center
  const D  = 0.20;   // depth front-to-back (flat pad)

  // ─── Stage rotations — cinematic choreography ──────────────────────────────
  const STAGE_ROTATIONS = [
    0,               // Front face hero — product reveal
    Math.PI * 0.20,  // Slight left — body contour + front waistband
    Math.PI * 0.45,  // Three-quarter side — depth + leg cuff profile
    Math.PI * 0.78,  // Three-quarter back — tape wings prominent
    Math.PI * 1.22,  // Near-front right — cinematic finale
  ];

  // Per-stage subtle camera zoom
  const STAGE_CAMERA_Z = [5.6, 5.2, 5.5, 5.1, 4.9];

  // ─── Init ──────────────────────────────────────────────────────────────────
  function init(canvasEl) {
    canvas = canvasEl;
    if (!canvas) return;
    const THREE = window.THREE;
    if (!THREE) { console.warn('CCDiaperThree: Three.js not loaded'); return; }

    const W = canvas.clientWidth || 640;
    const H = canvas.clientHeight || 640;

    renderer = new THREE.WebGLRenderer({
      canvas,
      antialias: true,
      alpha: true,
      powerPreference: 'high-performance',
    });
    renderer.setSize(W, H);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.08;
    renderer.outputColorSpace = THREE.SRGBColorSpace;

    scene = new THREE.Scene();
    scene.background = null;

    // Narrow FOV = compressed telephoto = premium product-photo feel
    camera = new THREE.PerspectiveCamera(30, W / H, 0.1, 100);
    camera.position.set(0, 0.04, 5.6);
    camera.lookAt(0, 0, 0);

    diaperGroup = buildDiaper(THREE);
    scene.add(diaperGroup);

    setupLights(THREE, scene);
    buildParticles(THREE, scene);
    bindEvents(canvas);

    const ro = new ResizeObserver(() => onResize(THREE));
    ro.observe(canvas.parentElement || canvas);

    isInitialized = true;
    return { renderer, scene, camera };
  }

  // ─── Dynamic Canvas Texture Generator ─────────────────────────────────────
  function createDiaperTexture(THREE) {
    const canvas = document.createElement('canvas');
    canvas.width = 1024;
    canvas.height = 1024;
    const ctx = canvas.getContext('2d');

    // Fill base warm white
    ctx.fillStyle = '#FAF8F5';
    ctx.fillRect(0, 0, 1024, 1024);

    // Draw fabric noise
    ctx.fillStyle = 'rgba(0, 0, 0, 0.015)';
    for (let i = 0; i < 35000; i++) {
      const rx = Math.random() * 1024;
      const ry = Math.random() * 1024;
      ctx.fillRect(rx, ry, 1, 1);
    }

    // Draw soft fabric ribbing lines (vertical ribs)
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.25)';
    ctx.lineWidth = 1;
    for (let x = 0; x < 1024; x += 4) {
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.lineTo(x, 1024);
      ctx.stroke();
    }

    // Draw Back Waistband (at the top of the canvas, which maps to back waist v = 1.0)
    ctx.fillStyle = '#9cbcd5';
    ctx.fillRect(0, 0, 1024, 65);
    // Draw elastic waistband gather lines
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.45)';
    ctx.lineWidth = 1.5;
    for (let x = 0; x < 1024; x += 6) {
      ctx.beginPath();
      ctx.moveTo(x, 5);
      ctx.lineTo(x, 60);
      ctx.stroke();
    }

    // Draw Front Waistband (at the bottom of the canvas, which maps to front waist v = -1.0)
    ctx.fillStyle = '#b0cfe5';
    ctx.fillRect(0, 959, 1024, 65);
    // Draw elastic waistband gather lines
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.45)';
    ctx.lineWidth = 1.5;
    for (let x = 0; x < 1024; x += 6) {
      ctx.beginPath();
      ctx.moveTo(x, 964);
      ctx.lineTo(x, 1019);
      ctx.stroke();
    }

    // Draw Absorbent Core Pad (quilted area Y = 230 to 790)
    const coreW = 240;
    const coreH = 560;
    const coreX = 512 - coreW / 2;
    const coreY = 230;

    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    if (ctx.roundRect) {
      ctx.roundRect(coreX, coreY, coreW, coreH, 30);
    } else {
      ctx.rect(coreX, coreY, coreW, coreH);
    }
    ctx.fill();

    // Quilting diamond stitch pattern
    ctx.strokeStyle = 'rgba(225, 220, 210, 0.65)';
    ctx.lineWidth = 1.5;
    ctx.save();
    ctx.beginPath();
    if (ctx.roundRect) {
      ctx.roundRect(coreX, coreY, coreW, coreH, 30);
    } else {
      ctx.rect(coreX, coreY, coreW, coreH);
    }
    ctx.clip();

    for (let offset = -600; offset < 1000; offset += 45) {
      ctx.beginPath();
      ctx.moveTo(coreX + offset, coreY);
      ctx.lineTo(coreX + offset + coreH, coreY + coreH);
      ctx.stroke();

      ctx.beginPath();
      ctx.moveTo(coreX + offset + coreH, coreY);
      ctx.lineTo(coreX + offset, coreY + coreH);
      ctx.stroke();
    }
    ctx.restore();

    // Landing Zone (front Y = 814 to 939)
    const lzY = 814;
    const lzH = 125;
    const lzW = 480;
    const lzX = 512 - lzW / 2;

    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    if (ctx.roundRect) {
      ctx.roundRect(lzX, lzY, lzW, lzH, 12);
    } else {
      ctx.rect(lzX, lzY, lzW, lzH);
    }
    ctx.fill();

    // Draw soft grid pattern inside landing zone
    ctx.strokeStyle = 'rgba(184, 214, 236, 0.2)';
    ctx.lineWidth = 1;
    for (let x = lzX; x < lzX + lzW; x += 12) {
      ctx.beginPath();
      ctx.moveTo(x, lzY);
      ctx.lineTo(x, lzY + lzH);
      ctx.stroke();
    }
    for (let y = lzY; y < lzY + lzH; y += 12) {
      ctx.beginPath();
      ctx.moveTo(lzX, y);
      ctx.lineTo(lzX + lzW, y);
      ctx.stroke();
    }

    ctx.strokeStyle = 'rgba(184, 214, 236, 0.45)';
    ctx.lineWidth = 2;
    ctx.stroke();

    // Cloud brand logo inside landing zone
    const logoX = 512;
    const logoY = lzY + lzH / 2;
    ctx.fillStyle = 'rgba(110, 164, 206, 0.75)';
    ctx.beginPath();
    ctx.arc(logoX, logoY - 4, 12, 0, Math.PI * 2);
    ctx.arc(logoX - 12, logoY, 8, 0, Math.PI * 2);
    ctx.arc(logoX + 12, logoY, 8, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(logoX - 20, logoY, 40, 10);
    ctx.fillStyle = 'rgba(110, 164, 206, 0.75)';
    ctx.fillRect(logoX - 16, logoY + 6, 32, 2.5);

    // Wetness Indicator stripe (vertical stripe down the middle Y = 140 to 880)
    ctx.strokeStyle = 'rgba(215, 175, 55, 0.85)';
    ctx.lineWidth = 6;
    ctx.lineCap = 'round';
    ctx.beginPath();
    ctx.moveTo(512, 140);
    ctx.lineTo(512, 880);
    ctx.stroke();

    const tex = new THREE.CanvasTexture(canvas);
    tex.wrapS = THREE.ClampToEdgeWrapping;
    tex.wrapT = THREE.ClampToEdgeWrapping;
    tex.needsUpdate = true;
    return tex;
  }

  // ─── Build Diaper ──────────────────────────────────────────────────────────
  function buildDiaper(THREE) {
    const group = new THREE.Group();

    // MATERIALS
    const texOuter = createDiaperTexture(THREE);
    const matOuter = new THREE.MeshStandardMaterial({
      map: texOuter,
      roughness: 0.82,
      metalness: 0.0,
    });

    const matInner = new THREE.MeshStandardMaterial({
      color: 0xffffff,
      roughness: 0.88,
      metalness: 0.0,
    });

    const matTape = new THREE.MeshStandardMaterial({
      color: 0xadd0ed,
      roughness: 0.40,
      metalness: 0.06,
      transparent: true,
      opacity: 0.94,
    });

    const matTab = new THREE.MeshStandardMaterial({
      color: 0x28395a,
      roughness: 0.36,
      metalness: 0.18,
    });

    const matRib = new THREE.MeshStandardMaterial({
      color: 0x1c2c48,
      roughness: 0.24,
      metalness: 0.24,
    });

    const matCuff = new THREE.MeshStandardMaterial({
      color: 0xbcd4ec,
      roughness: 0.60,
      metalness: 0.0,
    });

    const matCuffInner = new THREE.MeshStandardMaterial({
      color: 0xd8eaf8,
      roughness: 0.72,
      metalness: 0.0,
    });

    // Parametric curves math
    const M = 64; // columns (u)
    const N = 80; // rows (v)

    function getDiaperBasePoint(u, v) {
      // Hourglass width: wider at back (v = 1) than front (v = -1)
      const wFront = 0.44 + 0.38 * v * v;
      const wBack = 0.44 + 0.54 * v * v;
      let w = v < 0 ? wFront : wBack;

      // Waistband compression: elastic pulls waistband inward with subtle ruffling
      const waistFoldFreq = 28 * Math.PI;
      const waistFoldPattern = Math.sin(u * waistFoldFreq) + 0.3 * Math.sin(u * 1.5 * waistFoldFreq);
      const compression = 1.0 - 0.08 * Math.pow(Math.abs(v), 6) * (1.0 + 0.15 * waistFoldPattern);
      w = w * compression;

      // Leg gather ripple compression along the outer leg edges
      const legFoldFreq = 24 * Math.PI;
      const legFoldPattern = Math.sin(v * legFoldFreq) + 0.25 * Math.sin(v * 1.7 * legFoldFreq);
      const legRuffle = 1.0 - 0.04 * Math.pow(Math.abs(u), 6) * (1.0 - v * v) * (1.0 + 0.2 * legFoldPattern);
      w = w * legRuffle;

      // U-bend along length (v)
      const theta = v * 1.95;
      const z_c = -0.60 * Math.sin(theta);
      const y_c = -0.64 + 1.26 * v * v;

      // Horizontal cylinder-like pelvic wrapping around the waist & crotch
      const wrapAngle = 1.0 + 0.45 * v * v;
      const angle = u * wrapAngle;

      const x = (w / wrapAngle) * Math.sin(angle);
      
      // Base wrap depth in Z (wraps forward at back, wraps backward at front)
      let z = z_c + v * (w / wrapAngle) * (1.0 - Math.cos(angle));
      let y = y_c;

      // Leg openings curve upward around the thighs in Y
      const legCurve = 0.26 * (1.0 - v * v) * (u * u);
      y += legCurve;

      // Volumetric back seat bulge (buttocks)
      const seatBulge = 0.18 * Math.max(0, v) * Math.cos(u * Math.PI / 2) * (1.0 - v * v);
      z -= seatBulge;

      // Volumetric front bulge
      const frontBulge = 0.12 * Math.max(0, -v) * Math.cos(u * Math.PI / 2) * (1.0 - v * v);
      z += frontBulge;

      return new THREE.Vector3(x, y, z);
    }

    function getDiaperPointAndNormal(u, v) {
      const p = getDiaperBasePoint(u, v);

      // Tangent in U
      const eps = 0.005;
      const pU1 = getDiaperBasePoint(Math.max(-1, u - eps), v);
      const pU2 = getDiaperBasePoint(Math.min(1, u + eps), v);
      const tU = new THREE.Vector3().subVectors(pU2, pU1).normalize();

      // Tangent in V
      const pV1 = getDiaperBasePoint(u, Math.max(-1, v - eps));
      const pV2 = getDiaperBasePoint(u, Math.min(1, v + eps));
      const tV = new THREE.Vector3().subVectors(pV2, pV1).normalize();

      // Normal (oriented outwards)
      const normal = new THREE.Vector3().crossVectors(tV, tU).normalize();

      // Displacement gathers
      // 1. Waist gathers near v = 1 and v = -1 (vertical compression folds)
      const waistFoldFreq = 28 * Math.PI;
      const waistFoldPattern = Math.sin(u * waistFoldFreq) + 0.3 * Math.sin(u * 1.5 * waistFoldFreq);
      const g_w = 0.030 * waistFoldPattern * Math.pow(Math.abs(v), 5);

      // 2. Leg gathers near u = 1 and u = -1 (ruffled elastic gathers)
      const legFoldFreq = 24 * Math.PI;
      const legFoldPattern = Math.sin(v * legFoldFreq) + 0.25 * Math.sin(v * 1.7 * legFoldFreq);
      const g_l = 0.020 * legFoldPattern * Math.pow(Math.abs(u), 4) * (1.0 - v * v);

      // 3. Asymmetrical organic cloth wrinkles on loose side panels
      const sideMask = Math.pow(Math.abs(u), 2) * (1.0 - Math.pow(v, 4));
      const organicWrinkles = (0.012 * Math.sin(u * 5.0 + v * 8.0) + 0.008 * Math.cos(u * -4.0 + v * 11.0)) * sideMask;

      const disp = g_w + g_l + organicWrinkles;
      p.addScaledVector(normal, disp);

      return { point: p, normal: normal };
    }

    function getThickness(u, v) {
      const baseTh = 0.024;
      
      // Hourglass/rectangular core pad profile mask
      const uProfile = Math.max(0, 1.0 - Math.pow(Math.abs(u) / 0.54, 4));
      const vProfile = Math.max(0, 1.0 - Math.pow(Math.abs(v) / 0.78, 6));
      
      // Core thickness for absorbent bulk (thicker padded center)
      const coreTh = 0.090 * uProfile * vProfile;
      
      // Soft quilting pillow displacement inside the core region
      const quilt = 0.006 * Math.sin(u * 14.0) * Math.sin(v * 14.0) * uProfile * vProfile;
      
      return baseTh + coreTh + quilt;
    }

    const vertices = [];
    const normals = [];
    const uvs = [];
    const indices = [];

    // Outer grid vertices
    for (let j = 0; j < N; j++) {
      const v = -1 + 2 * j / (N - 1);
      for (let i = 0; i < M; i++) {
        const u = -1 + 2 * i / (M - 1);
        const { point, normal } = getDiaperPointAndNormal(u, v);
        const th = getThickness(u, v);

        const pOuter = new THREE.Vector3().addVectors(point, normal.clone().multiplyScalar(0.5 * th));
        vertices.push(pOuter.x, pOuter.y, pOuter.z);
        normals.push(normal.x, normal.y, normal.z);
        uvs.push(i / (M - 1), j / (N - 1));
      }
    }

    // Inner grid vertices
    for (let j = 0; j < N; j++) {
      const v = -1 + 2 * j / (N - 1);
      for (let i = 0; i < M; i++) {
        const u = -1 + 2 * i / (M - 1);
        const { point, normal } = getDiaperPointAndNormal(u, v);
        const th = getThickness(u, v);

        const pInner = new THREE.Vector3().addVectors(point, normal.clone().multiplyScalar(-0.5 * th));
        vertices.push(pInner.x, pInner.y, pInner.z);

        const invNormal = normal.clone().multiplyScalar(-1);
        normals.push(invNormal.x, invNormal.y, invNormal.z);
        uvs.push(i / (M - 1), j / (N - 1));
      }
    }

    const kOuter = (i, j) => j * M + i;
    const kInner = (i, j) => M * N + j * M + i;

    // Outer faces (Group 0 - Counterclockwise Winding for Front-Facing)
    const outerIndexStart = indices.length;
    for (let j = 0; j < N - 1; j++) {
      for (let i = 0; i < M - 1; i++) {
        const a = kOuter(i, j);
        const b = kOuter(i + 1, j);
        const c = kOuter(i + 1, j + 1);
        const d = kOuter(i, j + 1);
        indices.push(a, c, b);
        indices.push(a, d, c);
      }
    }
    const outerIndexCount = indices.length - outerIndexStart;

    // Inner faces (Group 1 - Clockwise Winding for Back-Facing)
    const innerIndexStart = indices.length;
    for (let j = 0; j < N - 1; j++) {
      for (let i = 0; i < M - 1; i++) {
        const a = kInner(i, j);
        const b = kInner(i + 1, j);
        const c = kInner(i + 1, j + 1);
        const d = kInner(i, j + 1);
        indices.push(a, b, c);
        indices.push(a, c, d);
      }
    }
    const innerIndexCount = indices.length - innerIndexStart;

    // Bridge faces
    const bridgeIndexStart = indices.length;
    // 1. Front waist edge
    for (let i = 0; i < M - 1; i++) {
      const oA = kOuter(i, 0);
      const oB = kOuter(i + 1, 0);
      const iA = kInner(i, 0);
      const iB = kInner(i + 1, 0);
      indices.push(oA, iA, iB);
      indices.push(oA, iB, oB);
    }
    // 2. Back waist edge
    for (let i = 0; i < M - 1; i++) {
      const oA = kOuter(i, N - 1);
      const oB = kOuter(i + 1, N - 1);
      const iA = kInner(i, N - 1);
      const iB = kInner(i + 1, N - 1);
      indices.push(oA, oB, iB);
      indices.push(oA, iB, iA);
    }
    // 3. Left leg edge
    for (let j = 0; j < N - 1; j++) {
      const oA = kOuter(0, j);
      const oB = kOuter(0, j + 1);
      const iA = kInner(0, j);
      const iB = kInner(0, j + 1);
      indices.push(oA, iB, iA);
      indices.push(oA, oB, iB);
    }
    // 4. Right leg edge
    for (let j = 0; j < N - 1; j++) {
      const oA = kOuter(M - 1, j);
      const oB = kOuter(M - 1, j + 1);
      const iA = kInner(M - 1, j);
      const iB = kInner(M - 1, j + 1);
      indices.push(oA, iA, iB);
      indices.push(oA, iB, oB);
    }
    const bridgeIndexCount = indices.length - bridgeIndexStart;

    const geom = new THREE.BufferGeometry();
    geom.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3));
    geom.setAttribute('normal', new THREE.Float32BufferAttribute(normals, 3));
    geom.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geom.setIndex(indices);

    geom.addGroup(0, outerIndexCount, 0);
    geom.addGroup(outerIndexCount, innerIndexCount, 1);
    geom.addGroup(outerIndexCount + innerIndexCount, bridgeIndexCount, 1);

    const diaperMesh = new THREE.Mesh(geom, [matOuter, matInner]);
    diaperMesh.castShadow = true;
    diaperMesh.receiveShadow = true;
    group.add(diaperMesh);

    // BENT TAPE WINGS (wrapping forward organically)
    [-1, 1].forEach(side => {
      const wingV = 0.72;
      const wingU = side * 0.96;
      const { point, normal } = getDiaperPointAndNormal(wingU, wingV);
      const th = getThickness(wingU, wingV);

      const pos = point.clone().addScaledVector(normal, 0.1 * th);

      const wingGroup = new THREE.Group();
      wingGroup.position.copy(pos);

      const angleY = Math.atan2(normal.x, normal.z);
      wingGroup.rotation.y = angleY - side * 0.45;
      wingGroup.rotation.x = 0.05 * side;

      const WING_W = 0.20;
      const WING_H = 0.13;
      const WING_D = 0.012;

      const wingGeom = new THREE.BoxGeometry(WING_W, WING_H, WING_D);
      wingGeom.translate(side * WING_W / 2, 0, 0);
      const wingMesh = new THREE.Mesh(wingGeom, matTape);
      wingMesh.castShadow = true;
      wingGroup.add(wingMesh);

      const TAB_W = 0.065;
      const TAB_H = WING_H * 0.84;
      const tabGeom = new THREE.BoxGeometry(TAB_W, TAB_H, WING_D + 0.005);
      tabGeom.translate(side * (WING_W + TAB_W / 2), 0, 0);
      const tabMesh = new THREE.Mesh(tabGeom, matTab);
      tabMesh.castShadow = true;
      wingGroup.add(tabMesh);

      for (let r = 0; r < 3; r++) {
        const ribGeom = new THREE.BoxGeometry(0.045, 0.006, WING_D + 0.008);
        ribGeom.translate(side * (WING_W + TAB_W / 2), (r - 1) * 0.024, 0.001);
        const ribMesh = new THREE.Mesh(ribGeom, matRib);
        wingGroup.add(ribMesh);
      }

      group.add(wingGroup);
    });

    // 3D BENT LEG CUFFS (following curved body contour perfectly)
    [-1, 1].forEach(side => {
      const cuffKeyPoints = [];
      for (let j = 0; j < N; j++) {
        const v = -1 + 2 * j / (N - 1);
        const { point, normal } = getDiaperPointAndNormal(side, v);
        const th = getThickness(side, v);
        const pCuff = point.clone().addScaledVector(normal, 0.55 * th);
        cuffKeyPoints.push(pCuff);
      }

      const curve = new THREE.CatmullRomCurve3(cuffKeyPoints);
      const cuffGeom = new THREE.TubeGeometry(curve, 40, 0.018, 8, false);
      const cuffMesh = new THREE.Mesh(cuffGeom, matCuff);
      cuffMesh.castShadow = true;
      group.add(cuffMesh);

      const innerCuffPoints = cuffKeyPoints.map((p, idx) => {
        const v = -1 + 2 * idx / (N - 1);
        const { normal } = getDiaperPointAndNormal(side, v);
        const inwardDir = new THREE.Vector3(-side, 0, 0).normalize();
        return p.clone().addScaledVector(inwardDir, 0.03).addScaledVector(normal, -0.4 * getThickness(side, v));
      });
      const innerCurve = new THREE.CatmullRomCurve3(innerCuffPoints);
      const innerCuffGeom = new THREE.TubeGeometry(innerCurve, 40, 0.012, 6, false);
      const innerCuffMesh = new THREE.Mesh(innerCuffGeom, matCuffInner);
      group.add(innerCuffMesh);
    });

    group.scale.set(1.15, 1.15, 1.15);
    return group;
  }

  // ─── Floating Ambient Particles ────────────────────────────────────────────
  function buildParticles(THREE, scene) {
    const count = 52;
    const pos   = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      const r     = 2.2 + Math.random() * 1.4;
      const theta = Math.random() * Math.PI * 2;
      const phi   = (Math.random() - 0.5) * Math.PI;
      pos[i*3]     = r * Math.cos(phi) * Math.cos(theta);
      pos[i*3 + 1] = r * Math.sin(phi);
      pos[i*3 + 2] = r * Math.cos(phi) * Math.sin(theta) * 0.26;
    }
    const geo = new THREE.BufferGeometry();
    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    particleSystem = new THREE.Points(geo, new THREE.PointsMaterial({
      size: 0.015, color: 0x8eb1d9,
      transparent: true, opacity: 0.28, sizeAttenuation: true,
    }));
    scene.add(particleSystem);
  }

  // ─── Premium Cinematic Lighting ────────────────────────────────────────────
  // Goal: clearly reveal fabric texture and shape without washing out the model
  function setupLights(THREE, scene) {
    // Key — warm, from upper-left-front. Defines the whole form.
    const key = new THREE.DirectionalLight(0xfff8f0, 2.0);
    key.position.set(-2.2, 3.6, 5.0);
    key.castShadow = true;
    key.shadow.mapSize.width  = 2048;
    key.shadow.mapSize.height = 2048;
    key.shadow.camera.near   = 1;
    key.shadow.camera.far    = 20;
    key.shadow.camera.left   = -4;
    key.shadow.camera.right  = 4;
    key.shadow.camera.top    = 4;
    key.shadow.camera.bottom = -4;
    key.shadow.bias   = -0.0005;
    key.shadow.radius = 4;
    scene.add(key);

    // Fill — cool blue from right. Softens key shadow.
    const fill = new THREE.DirectionalLight(0xd0e8f8, 0.62);
    fill.position.set(4.5, 0.5, 2.5);
    scene.add(fill);

    // Rim — warm champagne from lower-back. Edge separation from background.
    const rim = new THREE.DirectionalLight(0xffeec0, 0.55);
    rim.position.set(0.5, -1.8, -5.8);
    scene.add(rim);

    // Back-left rim — cool tint for depth
    const rimCool = new THREE.DirectionalLight(0x7aaed8, 0.38);
    rimCool.position.set(-3.0, 1.5, -4.8);
    scene.add(rimCool);

    // Top accent — highlights waistband + tape wing surfaces
    const top = new THREE.PointLight(0xffffff, 0.50, 9);
    top.position.set(0, 4.5, 2.5);
    scene.add(top);

    // Under-fill — softens harsh bottom shadow
    const under = new THREE.PointLight(0xeef5fc, 0.20, 7);
    under.position.set(0, -3.5, 2.2);
    scene.add(under);

    // Ambient — low so shadows have real weight
    const ambient = new THREE.AmbientLight(0xf5f2ec, 0.30);
    scene.add(ambient);

    // Hemisphere — sky blue / warm ground gradient
    const hemi = new THREE.HemisphereLight(0xddeaf8, 0xfaf0e0, 0.26);
    scene.add(hemi);
  }

  // ─── Interaction Events ────────────────────────────────────────────────────
  function bindEvents(canvas) {
    canvas.addEventListener('mousedown', e => {
      isDragging = true;
      prevMouseX = e.clientX;
      prevMouseY = e.clientY;
      canvas.style.cursor = 'grabbing';
    });
    window.addEventListener('mousemove', e => {
      if (!isDragging || !diaperGroup) return;
      const dx = (e.clientX - prevMouseX) * 0.005;
      const dy = (e.clientY - prevMouseY) * 0.003;
      targetRotY += dx;
      targetRotX = Math.max(-0.28, Math.min(0.36, targetRotX + dy));
      prevMouseX = e.clientX;
      prevMouseY = e.clientY;
    });
    window.addEventListener('mouseup', () => {
      isDragging = false;
      if (canvas) canvas.style.cursor = 'grab';
    });

    let prevTouchX = 0;
    canvas.addEventListener('touchstart', e => {
      prevTouchX = e.touches[0].clientX;
    }, { passive: true });
    canvas.addEventListener('touchmove', e => {
      if (!diaperGroup) return;
      targetRotY += (e.touches[0].clientX - prevTouchX) * 0.007;
      prevTouchX = e.touches[0].clientX;
    }, { passive: true });
  }

  // ─── Resize ────────────────────────────────────────────────────────────────
  function onResize(THREE) {
    if (!renderer || !camera || !canvas) return;
    const W = canvas.clientWidth;
    const H = canvas.clientHeight;
    if (W === 0 || H === 0) return;
    renderer.setSize(W, H, false);
    camera.aspect = W / H;
    camera.updateProjectionMatrix();
  }

  // ─── Animate Loop ──────────────────────────────────────────────────────────
  function animate() {
    animFrameId = requestAnimationFrame(animate);
    if (!isVisible || !renderer || !scene || !camera || !diaperGroup) return;

    floatTime += 0.006; // very slow — premium breathing

    // Ultra-smooth lerp: 0.022 idle / 0.10 while dragging
    const lF = isDragging ? 0.10 : 0.022;
    currentRotY += (targetRotY - currentRotY) * lF;
    currentRotX += (targetRotX - currentRotX) * lF;

    diaperGroup.rotation.y = currentRotY;
    diaperGroup.rotation.x = currentRotX;

    // Subtle float — barely visible, not bouncy
    diaperGroup.position.y = Math.sin(floatTime) * 0.018;

    // Imperceptible Z-axis breathing
    diaperGroup.rotation.z = Math.sin(floatTime * 0.55) * 0.007;

    // Camera gentle Y parallax drift
    cameraCurrY += (cameraTargetY - cameraCurrY) * 0.018;
    camera.position.y = cameraCurrY;

    // Camera smooth zoom per stage
    cameraCurrZ += (cameraTargetZ - cameraCurrZ) * 0.020;
    camera.position.z = cameraCurrZ;

    // Particles gentle orbit
    if (particleSystem) {
      particleSystem.rotation.y += 0.00032;
      particleSystem.rotation.x += 0.00016;
    }

    renderer.render(scene, camera);
  }

  // ─── Public: setStage ──────────────────────────────────────────────────────
  function setStage(index) {
    if (!isInitialized) return;
    const target  = STAGE_ROTATIONS[index] !== undefined ? STAGE_ROTATIONS[index] : 0;
    const diff    = target - targetRotY;
    const wrapped = ((diff + Math.PI * 3) % (Math.PI * 2)) - Math.PI;
    targetRotY    = targetRotY + wrapped;
    targetRotX    = 0.05; // reset tilt on stage change

    // Subtle per-stage camera choreography
    cameraTargetY = 0.04 + index * 0.012;
    cameraTargetZ = STAGE_CAMERA_Z[index] !== undefined ? STAGE_CAMERA_Z[index] : 5.6;
  }

  function setVisible(v) {
    isVisible = v;
    if (v && !animFrameId) animate();
  }

  function destroy() {
    if (animFrameId) cancelAnimationFrame(animFrameId);
    animFrameId = null;
    if (renderer) { renderer.dispose(); renderer = null; }
    isInitialized = false;
  }

  function getStageRotations() { return STAGE_ROTATIONS; }

  return { init, animate, setStage, setVisible, destroy, getStageRotations };
})();

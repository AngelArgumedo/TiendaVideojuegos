import { Component, AfterViewInit, ElementRef, ViewChild, OnDestroy, NgZone, HostListener } from '@angular/core';

// Declaramos 'THREE' y el 'SVGLoader' para que TypeScript sepa que existen
declare const THREE: any;

@Component({
  selector: 'app-hero-background',
  standalone: true,
  template: `<canvas #canvas class="hero-canvas"></canvas>`,
  styleUrl: './hero-background.component.scss'
})
export class HeroBackgroundComponent implements AfterViewInit, OnDestroy {
  @ViewChild('canvas') private canvasRef!: ElementRef;

  private scene!: any;
  private camera!: any;
  private renderer!: any;
  private gamepads: any[] = []; // Array para guardar nuestros mandos
  private frameId: number | null = null;
  private clock = new THREE.Clock();

  constructor(private ngZone: NgZone) {}

  ngAfterViewInit(): void {
    this.ngZone.runOutsideAngular(() => {
      this.initThreeJs();
      this.animate();
    });
  }

  ngOnDestroy(): void {
    if (this.frameId != null) {
      cancelAnimationFrame(this.frameId);
    }
    window.removeEventListener('resize', this.onWindowResize);
  }

  private get canvas(): HTMLCanvasElement {
    return this.canvasRef.nativeElement;
  }

  private initThreeJs(): void {
    // --- Configuración de la Escena ---
    this.scene = new THREE.Scene();
    this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    this.camera.position.z = 100;

    this.renderer = new THREE.WebGLRenderer({ canvas: this.canvas, antialias: true });
    this.renderer.setSize(window.innerWidth, window.innerHeight);
    this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    // --- Luces ---
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    this.scene.add(ambientLight);
    const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
    directionalLight.position.set(5, 5, 5);
    this.scene.add(directionalLight);

    // --- Creación de los Mandos ---
    const gamepadSvg = `
      <svg viewBox="0 0 24 24">
        <path d="M16.5,9L13.5,12L16.5,15H22V9M7.5,9H2V15H7.5L10.5,12L7.5,9M12,13.5L9,16.5V22H15V16.5L12,13.5M15,2H9V7.5L12,10.5L15,7.5V2Z" />
      </svg>`;

    const loader = new THREE.SVGLoader();
    const data = loader.parse(gamepadSvg);
    const paths = data.paths;
    const material = new THREE.MeshStandardMaterial({
        color: new THREE.Color(0x1a1a1a), // Un color oscuro, casi negro
        roughness: 0.7,
        metalness: 0.1,
    });

    // --- ¡CAMBIO! Aumentamos la cantidad de mandos ---
    const gamepadCount = 150;
    for (let i = 0; i < gamepadCount; i++) {
        const path = paths[0];
        const shapes = THREE.SVGLoader.createShapes(path);

        const extrudeSettings = {
            depth: 2,
            bevelEnabled: true,
            bevelSegments: 2,
            steps: 2,
            bevelSize: 1,
            bevelThickness: 1
        };

        const geometry = new THREE.ExtrudeGeometry(shapes, extrudeSettings);
        const mesh = new THREE.Mesh(geometry, material);

        // Posición y escala aleatorias
        mesh.position.set(
            (Math.random() - 0.5) * 300,
            (Math.random() - 0.5) * 300,
            (Math.random() - 0.5) * 300
        );
        const scale = Math.random() * 0.2 + 0.1;
        mesh.scale.set(scale, -scale, scale); // Invertimos Y para la orientación del SVG

        // Velocidad diagonal aleatoria
        (mesh as any).velocity = new THREE.Vector3(
            (Math.random() + 0.5) * 5,
            (Math.random() + 0.5) * 5,
            0
        );

        this.gamepads.push(mesh);
        this.scene.add(mesh);
    }

    window.addEventListener('resize', this.onWindowResize, false);
  }

  private animate(): void {
    this.frameId = requestAnimationFrame(() => this.animate());
    const delta = this.clock.getDelta();

    // Animamos cada mando
    this.gamepads.forEach(gamepad => {
      // Movemos el mando en diagonal
      gamepad.position.x += gamepad.velocity.x * delta;
      gamepad.position.y += gamepad.velocity.y * delta;

      // Rotación sutil
      gamepad.rotation.x += 0.1 * delta;
      gamepad.rotation.y += 0.1 * delta;

      // Si el mando se sale de la pantalla, lo reposicionamos al otro lado
      const bounds = {
          x: window.innerWidth / 15,
          y: window.innerHeight / 15
      };

      if (gamepad.position.x > bounds.x) {
          gamepad.position.x = -bounds.x;
          gamepad.position.y = (Math.random() - 0.5) * bounds.y * 2;
      }
      if (gamepad.position.y > bounds.y) {
          gamepad.position.y = -bounds.y;
          gamepad.position.x = (Math.random() - 0.5) * bounds.x * 2;
      }
    });

    this.renderer.render(this.scene, this.camera);
  }

  private onWindowResize = () => {
      this.camera.aspect = window.innerWidth / window.innerHeight;
      this.camera.updateProjectionMatrix();
      this.renderer.setSize(window.innerWidth, window.innerHeight);
  }
}

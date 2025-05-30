import React, { Suspense } from 'react';
import { Canvas } from '@react-three/fiber';
import { OrbitControls, Environment, PerspectiveCamera } from '@react-three/drei';
import FloatingCoins from './FloatingCoins';

interface Enhanced3DSceneProps {
  activeModel?: 'all';
}

const Enhanced3DScene: React.FC<Enhanced3DSceneProps> = ({ activeModel = 'all' }) => {
  return (
    <div className="absolute inset-0 opacity-30">
      <Canvas>
        <Suspense fallback={null}>
          <PerspectiveCamera makeDefault position={[0, 0, 15]} fov={60} />
          
          {/* Entorno y luces */}
          <Environment preset="night" />
          <ambientLight intensity={0.3} color="#1F2029" />
          <directionalLight position={[10, 10, 5]} intensity={1.5} color="#fecd02" castShadow />
          <pointLight position={[-10, -10, -10]} intensity={1} color="#fecd02" />
          <spotLight 
            position={[0, 25, 0]} 
            intensity={1.2} 
            angle={Math.PI / 4} 
            penumbra={1} 
            color="#fecd02"
            castShadow
          />

          {/* Solo mostramos FloatingCoins */}
          <FloatingCoins />
          
          {/* Controles */}
          <OrbitControls
            enableZoom={true}
            enablePan={true}
            autoRotate={true}
            autoRotateSpeed={0.5}
            minDistance={8}
            maxDistance={25}
            maxPolarAngle={Math.PI / 1.8}
          />
        </Suspense>
      </Canvas>
    </div>
  );
};

export default Enhanced3DScene;

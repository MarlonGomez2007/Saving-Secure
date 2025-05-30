
import React, { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Float, Text3D } from '@react-three/drei';
import * as THREE from 'three';

const BusinessModel = () => {
  const groupRef = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (groupRef.current) {
      groupRef.current.rotation.y = state.clock.elapsedTime * 0.3;
    }
  });

  return (
    <Float speed={1.2} rotationIntensity={0.2} floatIntensity={0.4}>
      <group ref={groupRef}>
        {/* Gráfico de barras */}
        {[0, 1, 2].map((index) => (
          <mesh key={index} position={[index * 0.4 - 0.4, (index + 1) * 0.3, 0]}>
            <boxGeometry args={[0.2, (index + 1) * 0.6, 0.2]} />
            <meshStandardMaterial 
              color="#8B5CF6" 
              metalness={0.7} 
              roughness={0.2} 
              emissive="#8B5CF6"
              emissiveIntensity={0.1}
            />
          </mesh>
        ))}

        {/* Flecha ascendente */}
        <Float speed={3} rotationIntensity={0.5} floatIntensity={1}>
          <mesh position={[1, 1.2, 0]} rotation={[0, 0, -Math.PI / 4]}>
            <coneGeometry args={[0.15, 0.6, 3]} />
            <meshStandardMaterial 
              color="#fecd02" 
              metalness={0.8} 
              roughness={0.1} 
              emissive="#fecd02"
              emissiveIntensity={0.2}
            />
          </mesh>
        </Float>

        {/* Monedas flotantes */}
        {[0, 1, 2].map((index) => (
          <Float key={index} speed={2 + index * 0.3} rotationIntensity={1}>
            <mesh 
              position={[
                Math.sin(index * 2) * 1.5, 
                Math.cos(index * 2) * 0.5 + 1, 
                Math.cos(index * 3) * 0.5
              ]} 
              scale={0.2}
            >
              <cylinderGeometry args={[0.3, 0.3, 0.1, 16]} />
              <meshStandardMaterial 
                color="#fecd02" 
                metalness={0.9} 
                roughness={0.1} 
              />
            </mesh>
          </Float>
        ))}

        {/* Texto */}
        <Text3D
          font="/fonts/helvetiker_regular.typeface.json"
          size={0.1}
          height={0.02}
          position={[-0.6, -1.5, 0]}
        >
          EMPRENDEDOR
          <meshStandardMaterial color="#fecd02" />
        </Text3D>
      </group>
    </Float>
  );
};

export default BusinessModel;

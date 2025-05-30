
import React, { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Float, Text3D } from '@react-three/drei';
import * as THREE from 'three';

const InvestmentModel = () => {
  const groupRef = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (groupRef.current) {
      groupRef.current.rotation.y = Math.sin(state.clock.elapsedTime * 0.6) * 0.2;
    }
  });

  return (
    <Float speed={1.6} rotationIntensity={0.5} floatIntensity={0.7}>
      <group ref={groupRef}>
        {/* Diamante principal */}
        <mesh position={[0, 0, 0]}>
          <octahedronGeometry args={[0.8, 0]} />
          <meshStandardMaterial 
            color="#fecd02" 
            metalness={0.9} 
            roughness={0.1} 
            emissive="#fecd02"
            emissiveIntensity={0.3}
          />
        </mesh>

        {/* Gráfico de líneas ascendentes */}
        {[0, 1, 2, 3, 4].map((index) => (
          <mesh key={index} position={[index * 0.3 - 0.6, index * 0.2 - 0.4, 0]}>
            <cylinderGeometry args={[0.05, 0.05, 0.4 + index * 0.2, 8]} />
            <meshStandardMaterial 
              color="#1F2029" 
              metalness={0.6} 
              roughness={0.3} 
            />
          </mesh>
        ))}

        {/* Monedas orbitando */}
        {[0, 1, 2, 3, 4, 5].map((index) => {
          const angle = (index * Math.PI * 2) / 6;
          const radius = 1.5;
          const x = Math.cos(angle) * radius;
          const z = Math.sin(angle) * radius;
          
          return (
            <Float key={index} speed={2 + index * 0.3} rotationIntensity={1}>
              <mesh position={[x, Math.sin(index * 0.5) * 0.3, z]} scale={0.2}>
                <cylinderGeometry args={[0.4, 0.4, 0.1, 16]} />
                <meshStandardMaterial 
                  color="#fecd02" 
                  metalness={0.9} 
                  roughness={0.1} 
                />
              </mesh>
            </Float>
          );
        })}

        {/* Flecha de crecimiento */}
        <Float speed={2.5} rotationIntensity={0.4} floatIntensity={1}>
          <mesh position={[1.2, 1.5, 0]} rotation={[0, 0, -Math.PI / 4]}>
            <coneGeometry args={[0.2, 0.8, 6]} />
            <meshStandardMaterial 
              color="#fecd02" 
              metalness={0.8} 
              roughness={0.2} 
              emissive="#fecd02"
              emissiveIntensity={0.2}
            />
          </mesh>
        </Float>

        {/* Texto */}
        <Text3D
          font="/fonts/helvetiker_regular.typeface.json"
          size={0.1}
          height={0.02}
          position={[-0.6, -2, 0]}
        >
          INVERSIONES
          <meshStandardMaterial color="#fecd02" />
        </Text3D>
      </group>
    </Float>
  );
};

export default InvestmentModel;

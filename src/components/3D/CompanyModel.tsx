
import React, { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Float, Text3D } from '@react-three/drei';
import * as THREE from 'three';

const CompanyModel = () => {
  const groupRef = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (groupRef.current) {
      groupRef.current.rotation.y = Math.sin(state.clock.elapsedTime * 0.2) * 0.4;
    }
  });

  return (
    <Float speed={1} rotationIntensity={0.1} floatIntensity={0.3}>
      <group ref={groupRef}>
        {/* Edificio principal */}
        <mesh position={[0, 0.5, 0]}>
          <boxGeometry args={[1, 2, 0.8]} />
          <meshStandardMaterial 
            color="#1E40AF" 
            metalness={0.4} 
            roughness={0.6} 
          />
        </mesh>

        {/* Ventanas */}
        {Array.from({ length: 6 }, (_, i) => {
          const row = Math.floor(i / 2);
          const col = i % 2;
          return (
            <mesh 
              key={i} 
              position={[-0.2 + col * 0.4, 0.2 + row * 0.4, 0.41]}
            >
              <boxGeometry args={[0.15, 0.15, 0.02]} />
              <meshStandardMaterial 
                color="#fecd02" 
                metalness={0.8} 
                roughness={0.1} 
                emissive="#fecd02"
                emissiveIntensity={0.3}
              />
            </mesh>
          );
        })}

        {/* Red de conexiones */}
        <Float speed={2} rotationIntensity={0.3}>
          <mesh position={[0, 2, 0]}>
            <octahedronGeometry args={[0.4, 2]} />
            <meshStandardMaterial 
              color="#DC2626" 
              metalness={0.7} 
              roughness={0.2} 
              wireframe
            />
          </mesh>
        </Float>

        {/* Documentos flotantes */}
        {[0, 1, 2].map((index) => (
          <Float key={index} speed={1.5 + index * 0.5} rotationIntensity={0.8}>
            <mesh 
              position={[
                1.5 + Math.sin(index) * 0.5, 
                index * 0.6, 
                Math.cos(index) * 0.5
              ]} 
              rotation={[Math.PI / 4, index, 0]}
              scale={0.3}
            >
              <boxGeometry args={[0.6, 0.8, 0.05]} />
              <meshStandardMaterial 
                color="white" 
                metalness={0.1} 
                roughness={0.8} 
              />
            </mesh>
          </Float>
        ))}

        {/* Texto */}
        <Text3D
          font="/fonts/helvetiker_regular.typeface.json"
          size={0.12}
          height={0.02}
          position={[-0.4, -1.8, 0]}
        >
          EMPRESA
          <meshStandardMaterial color="#fecd02" />
        </Text3D>
      </group>
    </Float>
  );
};

export default CompanyModel;

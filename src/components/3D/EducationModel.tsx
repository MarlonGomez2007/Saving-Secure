
import React, { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Float, Text3D } from '@react-three/drei';
import * as THREE from 'three';

const EducationModel = () => {
  const groupRef = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (groupRef.current) {
      groupRef.current.rotation.y = Math.sin(state.clock.elapsedTime * 0.3) * 0.4;
    }
  });

  return (
    <Float speed={1.1} rotationIntensity={0.3} floatIntensity={0.5}>
      <group ref={groupRef}>
        {/* Libro abierto */}
        <mesh position={[0, 0, 0]} rotation={[0, 0, Math.PI / 12]}>
          <boxGeometry args={[1.5, 0.1, 1]} />
          <meshStandardMaterial 
            color="#1F2029" 
            metalness={0.1} 
            roughness={0.8} 
          />
        </mesh>
        
        <mesh position={[0, 0.05, 0]} rotation={[0, 0, -Math.PI / 12]}>
          <boxGeometry args={[1.5, 0.1, 1]} />
          <meshStandardMaterial 
            color="#1F2029" 
            metalness={0.1} 
            roughness={0.8} 
          />
        </mesh>

        {/* Páginas */}
        {[0, 1, 2].map((index) => (
          <mesh key={index} position={[0, 0.1 + index * 0.02, 0]}>
            <boxGeometry args={[1.4, 0.01, 0.9]} />
            <meshStandardMaterial 
              color="#fecd02" 
              metalness={0.2} 
              roughness={0.7} 
              transparent
              opacity={0.8}
            />
          </mesh>
        ))}

        {/* Bombilla de idea */}
        <Float speed={2.5} rotationIntensity={0.6} floatIntensity={1.2}>
          <mesh position={[0, 1.5, 0]} scale={0.4}>
            <sphereGeometry args={[0.5, 16, 16]} />
            <meshStandardMaterial 
              color="#fecd02" 
              metalness={0.8} 
              roughness={0.1} 
              emissive="#fecd02"
              emissiveIntensity={0.4}
            />
          </mesh>
        </Float>

        {/* Letras flotantes */}
        {['A', 'B', 'C'].map((letter, index) => (
          <Float key={index} speed={1.8 + index * 0.4} rotationIntensity={1}>
            <Text3D
              font="/fonts/helvetiker_regular.typeface.json"
              size={0.2}
              height={0.05}
              position={[
                1.5 + Math.sin(index * 2) * 0.5, 
                Math.cos(index * 2) * 0.8, 
                Math.sin(index * 1.5) * 0.3
              ]}
            >
              {letter}
              <meshStandardMaterial color="#fecd02" />
            </Text3D>
          </Float>
        ))}

        {/* Texto */}
        <Text3D
          font="/fonts/helvetiker_regular.typeface.json"
          size={0.1}
          height={0.02}
          position={[-0.5, -1.5, 0]}
        >
          EDUCACIÓN
          <meshStandardMaterial color="#fecd02" />
        </Text3D>
      </group>
    </Float>
  );
};

export default EducationModel;

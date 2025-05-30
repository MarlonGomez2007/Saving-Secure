
import React, { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Float, Text3D } from '@react-three/drei';
import * as THREE from 'three';

const FamilyModel = () => {
  const groupRef = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (groupRef.current) {
      groupRef.current.rotation.y = Math.sin(state.clock.elapsedTime * 0.5) * 0.3;
    }
  });

  return (
    <Float speed={1.5} rotationIntensity={0.3} floatIntensity={0.5}>
      <group ref={groupRef}>
        {/* Casa */}
        <mesh position={[0, 0, 0]}>
          <boxGeometry args={[1.5, 1, 1]} />
          <meshStandardMaterial 
            color="#4F46E5" 
            metalness={0.1} 
            roughness={0.4} 
          />
        </mesh>
        
        {/* Techo */}
        <mesh position={[0, 0.8, 0]} rotation={[0, Math.PI / 4, 0]}>
          <coneGeometry args={[1.2, 0.6, 4]} />
          <meshStandardMaterial 
            color="#EF4444" 
            metalness={0.2} 
            roughness={0.3} 
          />
        </mesh>

        {/* Corazón flotante */}
        <Float speed={2} rotationIntensity={1} floatIntensity={1}>
          <mesh position={[1.5, 1.5, 0]} scale={0.3}>
            <sphereGeometry args={[0.5, 16, 16]} />
            <meshStandardMaterial 
              color="#fecd02" 
              metalness={0.8} 
              roughness={0.2} 
              emissive="#fecd02"
              emissiveIntensity={0.3}
            />
          </mesh>
        </Float>

        {/* Texto */}
        <Text3D
          font="/fonts/helvetiker_regular.typeface.json"
          size={0.15}
          height={0.02}
          position={[-0.5, -1.5, 0]}
        >
          FAMILIA
          <meshStandardMaterial color="#fecd02" />
        </Text3D>
      </group>
    </Float>
  );
};

export default FamilyModel;


import React, { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Float, Text3D } from '@react-three/drei';
import * as THREE from 'three';

const HealthModel = () => {
  const groupRef = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (groupRef.current) {
      groupRef.current.rotation.y = Math.sin(state.clock.elapsedTime * 0.4) * 0.3;
    }
  });

  return (
    <Float speed={1.3} rotationIntensity={0.2} floatIntensity={0.4}>
      <group ref={groupRef}>
        {/* Cruz médica */}
        <mesh position={[0, 0, 0]}>
          <boxGeometry args={[0.8, 0.2, 0.1]} />
          <meshStandardMaterial 
            color="#fecd02" 
            metalness={0.8} 
            roughness={0.2} 
            emissive="#fecd02"
            emissiveIntensity={0.3}
          />
        </mesh>
        
        <mesh position={[0, 0, 0]}>
          <boxGeometry args={[0.2, 0.8, 0.1]} />
          <meshStandardMaterial 
            color="#fecd02" 
            metalness={0.8} 
            roughness={0.2} 
            emissive="#fecd02"
            emissiveIntensity={0.3}
          />
        </mesh>

        {/* Corazón pulsante */}
        <Float speed={3} rotationIntensity={0.5} floatIntensity={1}>
          <mesh position={[1.2, 0.8, 0]} scale={0.3}>
            <sphereGeometry args={[0.4, 16, 16]} />
            <meshStandardMaterial 
              color="#DC2626" 
              metalness={0.6} 
              roughness={0.3} 
              emissive="#DC2626"
              emissiveIntensity={0.2}
            />
          </mesh>
        </Float>

        {/* Píldoras flotantes */}
        {[0, 1, 2, 3].map((index) => (
          <Float key={index} speed={2 + index * 0.3} rotationIntensity={0.8}>
            <mesh 
              position={[
                Math.sin(index * 1.5) * 1.5, 
                Math.cos(index * 1.5) * 0.8, 
                Math.sin(index * 2) * 0.5
              ]} 
              scale={0.15}
            >
              <capsuleGeometry args={[0.3, 0.6, 4, 8]} />
              <meshStandardMaterial 
                color={index % 2 === 0 ? "#fecd02" : "#1F2029"} 
                metalness={0.7} 
                roughness={0.3} 
              />
            </mesh>
          </Float>
        ))}

        {/* Texto */}
        <Text3D
          font="/fonts/helvetiker_regular.typeface.json"
          size={0.12}
          height={0.02}
          position={[-0.3, -1.5, 0]}
        >
          SALUD
          <meshStandardMaterial color="#fecd02" />
        </Text3D>
      </group>
    </Float>
  );
};

export default HealthModel;


import React, { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Float, Text3D } from '@react-three/drei';
import * as THREE from 'three';

const FriendsModel = () => {
  const groupRef = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (groupRef.current) {
      groupRef.current.rotation.y = Math.sin(state.clock.elapsedTime * 0.7) * 0.2;
    }
  });

  return (
    <Float speed={1.8} rotationIntensity={0.4} floatIntensity={0.6}>
      <group ref={groupRef}>
        {/* Círculo de amigos */}
        {[0, 1, 2, 3].map((index) => {
          const angle = (index * Math.PI * 2) / 4;
          const x = Math.cos(angle) * 0.8;
          const z = Math.sin(angle) * 0.8;
          
          return (
            <Float key={index} speed={2 + index * 0.2} rotationIntensity={0.5}>
              <mesh position={[x, Math.sin(index) * 0.2, z]} scale={0.4}>
                <sphereGeometry args={[0.3, 16, 16]} />
                <meshStandardMaterial 
                  color={index % 2 === 0 ? "#10B981" : "#06B6D4"} 
                  metalness={0.6} 
                  roughness={0.3} 
                />
              </mesh>
            </Float>
          );
        })}

        {/* Conexiones */}
        <mesh>
          <torusGeometry args={[0.8, 0.05, 8, 32]} />
          <meshStandardMaterial 
            color="#fecd02" 
            metalness={0.8} 
            roughness={0.2} 
            transparent
            opacity={0.7}
          />
        </mesh>

        {/* Texto */}
        <Text3D
          font="/fonts/helvetiker_regular.typeface.json"
          size={0.12}
          height={0.02}
          position={[-0.4, -1.5, 0]}
        >
          AMIGOS
          <meshStandardMaterial color="#fecd02" />
        </Text3D>
      </group>
    </Float>
  );
};

export default FriendsModel;

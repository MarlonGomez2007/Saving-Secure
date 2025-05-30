
import React, { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Text3D } from '@react-three/drei';
import * as THREE from 'three';

const FloatingCoin = ({ position }: { position: [number, number, number] }) => {
  const meshRef = useRef<THREE.Mesh>(null);

  useFrame((state) => {
    if (meshRef.current) {
      meshRef.current.rotation.y += 0.01;
      meshRef.current.position.y += Math.sin(state.clock.elapsedTime + position[0]) * 0.005;
    }
  });

  return (
    <group position={position}>
      <mesh ref={meshRef}>
        <cylinderGeometry args={[0.3, 0.3, 0.1, 32]} />
        <meshStandardMaterial 
          color="#fecd02" 
          metalness={0.8} 
          roughness={0.2} 
        />
      </mesh>
      <Text3D
        font="/fonts/helvetiker_regular.typeface.json"
        size={0.15}
        height={0.02}
        position={[0, 0, 0.06]}
      >
        $
        <meshStandardMaterial color="#000000" />
      </Text3D>
    </group>
  );
};

const FloatingCoins = () => {
  const coins = [
    [-2, 1, 0],
    [2, -1, 0],
    [-1, -2, 0],
    [3, 2, 0],
    [-3, 0, 0],
  ] as [number, number, number][];

  return (
    <>
      {coins.map((position, index) => (
        <FloatingCoin key={index} position={position} />
      ))}
    </>
  );
};

export default FloatingCoins;

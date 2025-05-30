
import React, { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import { Float, Text3D } from '@react-three/drei';
import * as THREE from 'three';

const TravelModel = () => {
  const groupRef = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (groupRef.current) {
      groupRef.current.rotation.y = state.clock.elapsedTime * 0.2;
    }
  });

  return (
    <Float speed={1.4} rotationIntensity={0.4} floatIntensity={0.6}>
      <group ref={groupRef}>
        {/* Avión */}
        <mesh position={[0, 0, 0]} rotation={[0, Math.PI / 2, 0]}>
          <coneGeometry args={[0.3, 1.5, 8]} />
          <meshStandardMaterial 
            color="#fecd02" 
            metalness={0.8} 
            roughness={0.2} 
          />
        </mesh>
        
        {/* Alas */}
        <mesh position={[-0.3, 0, 0]} rotation={[0, 0, Math.PI / 2]}>
          <boxGeometry args={[0.1, 1.2, 0.2]} />
          <meshStandardMaterial 
            color="#1F2029" 
            metalness={0.6} 
            roughness={0.4} 
          />
        </mesh>

        {/* Globo terráqueo */}
        <Float speed={2} rotationIntensity={1} floatIntensity={0.8}>
          <mesh position={[0, -1.5, 0]} scale={0.6}>
            <sphereGeometry args={[0.8, 32, 32]} />
            <meshStandardMaterial 
              color="#1F2029" 
              metalness={0.3} 
              roughness={0.7} 
            />
          </mesh>
        </Float>

        {/* Continentes */}
        {[0, 1, 2, 3, 4].map((index) => (
          <Float key={index} speed={1.5 + index * 0.2} rotationIntensity={0.5}>
            <mesh 
              position={[
                Math.sin(index * 1.2) * 0.5, 
                -1.5 + Math.cos(index * 1.2) * 0.3, 
                Math.cos(index * 1.5) * 0.5
              ]} 
              scale={0.15}
            >
              <sphereGeometry args={[0.2, 8, 8]} />
              <meshStandardMaterial 
                color="#fecd02" 
                metalness={0.5} 
                roughness={0.5} 
              />
            </mesh>
          </Float>
        ))}

        {/* Nubes */}
        {[0, 1, 2].map((index) => (
          <Float key={index} speed={3 + index * 0.5} rotationIntensity={0.3} floatIntensity={1.5}>
            <mesh 
              position={[
                2 + Math.sin(index * 2) * 0.8, 
                1 + Math.cos(index * 1.5) * 0.5, 
                Math.sin(index * 3) * 0.6
              ]} 
              scale={0.3}
            >
              <sphereGeometry args={[0.4, 12, 12]} />
              <meshStandardMaterial 
                color="#fecd02" 
                metalness={0.1} 
                roughness={0.9} 
                transparent
                opacity={0.7}
              />
            </mesh>
          </Float>
        ))}

        {/* Texto */}
        <Text3D
          font="/fonts/helvetiker_regular.typeface.json"
          size={0.12}
          height={0.02}
          position={[-0.4, -2.5, 0]}
        >
          VIAJES
          <meshStandardMaterial color="#fecd02" />
        </Text3D>
      </group>
    </Float>
  );
};

export default TravelModel;

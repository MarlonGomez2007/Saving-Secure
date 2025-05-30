
import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { useInView } from 'framer-motion';
import { useRef } from 'react';
import { Home, Users, TrendingUp, Building, Heart, GraduationCap, Plane, TrendingDown } from 'lucide-react';
import Enhanced3DScene from './3D/Enhanced3DScene';

const FeaturesSection = () => {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: "-100px" });
  const [activeModel, setActiveModel] = useState<'family' | 'friends' | 'business' | 'company' | 'health' | 'education' | 'travel' | 'investment' | 'all'>('all');

  const features = [
    {
      id: 'family' as const,
      title: "Economía Familiar",
      description: "Gestiona el presupuesto familiar de manera inteligente con herramientas diseñadas para toda la familia.",
      icon: Home,
      benefits: ["Presupuesto compartido", "Metas familiares", "Control de gastos", "Educación financiera"]
    },
    {
      id: 'friends' as const,
      title: "Grupo de Amigos", 
      description: "Divide gastos de viajes y eventos grupales de manera justa y transparente entre amigos.",
      icon: Users,
      benefits: ["División equitativa", "Pagos grupales", "Recordatorios automáticos", "Historial transparente"]
    },
    {
      id: 'business' as const,
      title: "Emprendedores",
      description: "Herramientas especializadas para emprendedores que buscan controlar sus finanzas de manera eficiente.",
      icon: TrendingUp,
      benefits: ["Finanzas personales", "Control de inversión", "Proyecciones financieras", "Análisis de rentabilidad"]
    },
    {
      id: 'company' as const,
      title: "Empresas",
      description: "Soluciones empresariales completas para gestión financiera avanzada con reportes y análisis.",
      icon: Building,
      benefits: ["Gestión de flujo de caja", "Reportes avanzados", "Facturación digital", "Control de inventarios"]
    },
    {
      id: 'health' as const,
      title: "Gastos de Salud",
      description: "Controla y planifica todos tus gastos médicos y de salud de manera organizada.",
      icon: Heart,
      benefits: ["Seguimiento médico", "Gastos farmacéuticos", "Seguros de salud", "Presupuesto preventivo"]
    },
    {
      id: 'education' as const,
      title: "Educación",
      description: "Planifica y gestiona los gastos educativos para ti o tu familia a largo plazo.",
      icon: GraduationCap,
      benefits: ["Matrícula universitaria", "Cursos especializados", "Material académico", "Ahorro educativo"]
    },
    {
      id: 'travel' as const,
      title: "Viajes",
      description: "Organiza y controla todos los gastos de tus viajes desde la planificación hasta el regreso.",
      icon: Plane,
      benefits: ["Presupuesto de viaje", "Gastos por destino", "Moneda extranjera", "Reservas y actividades"]
    },
    {
      id: 'investment' as const,
      title: "Inversiones",
      description: "Gestiona tu portafolio de inversiones y realiza seguimiento de tus activos financieros.",
      icon: TrendingDown,
      benefits: ["Portfolio tracking", "Análisis de ROI", "Diversificación", "Rebalanceo automático"]
    }
  ];

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.15,
        delayChildren: 0.3
      }
    }
  };

  const itemVariants = {
    hidden: { opacity: 0, y: 60, scale: 0.9 },
    visible: {
      opacity: 1,
      y: 0,
      scale: 1,
      transition: {
        duration: 0.8,
        ease: "easeOut"
      }
    }
  };

  return (
    <section id="features" ref={ref} className="py-32 px-6 relative overflow-hidden min-h-screen bg-gradient-to-br from-[rgba(31,32,41,1)] via-[rgba(31,32,41,0.95)] to-[rgba(31,32,41,1)]">
      {/* Escena 3D mejorada */}
      <Enhanced3DScene activeModel={activeModel} />

      {/* Efectos de fondo con colores específicos */}
      <div className="absolute inset-0">
        <motion.div 
          className="absolute top-0 left-1/4 w-96 h-96 bg-[#fecd02]/5 rounded-full blur-3xl"
          animate={{
            scale: [1, 1.3, 1],
            opacity: [0.05, 0.15, 0.05],
          }}
          transition={{
            duration: 6,
            repeat: Infinity,
            ease: "easeInOut",
          }}
        />
        <motion.div 
          className="absolute bottom-0 right-1/4 w-96 h-96 bg-[#fecd02]/8 rounded-full blur-3xl"
          animate={{
            scale: [1.3, 1, 1.3],
            opacity: [0.08, 0.03, 0.08],
          }}
          transition={{
            duration: 6,
            repeat: Infinity,
            ease: "easeInOut",
            delay: 3,
          }}
        />
        <motion.div 
          className="absolute top-1/2 left-1/2 w-[600px] h-[600px] bg-[#fecd02]/3 rounded-full blur-3xl transform -translate-x-1/2 -translate-y-1/2"
          animate={{
            scale: [1, 1.2, 1],
            rotate: [0, 180, 360],
          }}
          transition={{
            duration: 20,
            repeat: Infinity,
            ease: "linear",
          }}
        />
      </div>

      <div className="max-w-7xl mx-auto relative z-10">
        {/* Header mejorado */}
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          animate={isInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 1.2 }}
          className="text-center mb-24"
        >
          <motion.h2 
            className="text-7xl md:text-8xl lg:text-9xl font-bold mb-8 text-white leading-tight"
            animate={{
              textShadow: [
                "0 0 20px rgba(254, 205, 2, 0.3)",
                "0 0 40px rgba(254, 205, 2, 0.6)",
                "0 0 20px rgba(254, 205, 2, 0.3)",
              ],
            }}
            transition={{
              duration: 4,
              repeat: Infinity,
              ease: "easeInOut",
            }}
          >
            Casos de <span className="text-[#fecd02] drop-shadow-2xl">Uso</span>
          </motion.h2>
          <motion.p 
            className="text-2xl text-gray-300 max-w-4xl mx-auto leading-relaxed"
            initial={{ opacity: 0 }}
            animate={isInView ? { opacity: 1 } : {}}
            transition={{ delay: 0.5, duration: 1 }}
          >
            Descubre cómo Saving Secure se adapta perfectamente a cada aspecto de tu vida financiera
          </motion.p>

          {/* Selector de modelos 3D mejorado */}
          <motion.div
            className="flex flex-wrap justify-center gap-3 mt-12"
            initial={{ opacity: 0, y: 30 }}
            animate={isInView ? { opacity: 1, y: 0 } : {}}
            transition={{ delay: 0.7, duration: 0.8 }}
          >
            <motion.button
              onClick={() => setActiveModel('all')}
              className={`px-8 py-4 rounded-full font-bold text-lg transition-all duration-500 ${
                activeModel === 'all' 
                  ? 'bg-[#fecd02] text-[#1F2029] shadow-2xl shadow-[#fecd02]/30' 
                  : 'bg-[#1F2029]/80 border-2 border-[#fecd02]/30 text-[#fecd02] hover:bg-[#fecd02]/10 hover:border-[#fecd02]/60'
              }`}
              whileHover={{ scale: 1.05, y: -2 }}
              whileTap={{ scale: 0.95 }}
            >
              Ver Todo
            </motion.button>
            {features.slice(0, 4).map((feature) => (
              <motion.button
                key={feature.id}
                onClick={() => setActiveModel(feature.id)}
                className={`px-6 py-3 rounded-full font-semibold text-base transition-all duration-500 ${
                  activeModel === feature.id 
                    ? 'bg-[#fecd02] text-[#1F2029] shadow-xl shadow-[#fecd02]/25' 
                    : 'bg-[#1F2029]/70 border border-[#fecd02]/25 text-[#fecd02] hover:bg-[#fecd02]/10 hover:border-[#fecd02]/50'
                }`}
                whileHover={{ scale: 1.05, y: -2 }}
                whileTap={{ scale: 0.95 }}
              >
                {feature.title}
              </motion.button>
            ))}
          </motion.div>
        </motion.div>

        {/* Grid de características mejorado */}
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate={isInView ? "visible" : "hidden"}
          className="grid lg:grid-cols-2 xl:grid-cols-2 gap-8"
        >
          {features.map((feature, index) => {
            const IconComponent = feature.icon;
            const isActive = activeModel === feature.id || activeModel === 'all';
            
            return (
              <motion.div
                key={index}
                variants={itemVariants}
                whileHover={{ 
                  scale: 1.03,
                  rotateY: 2,
                  z: 50
                }}
                onClick={() => setActiveModel(feature.id)}
                className={`relative group cursor-pointer bg-gradient-to-br from-[#1F2029]/60 via-[#1F2029]/70 to-[#1F2029]/80 backdrop-blur-xl border-2 rounded-3xl p-10 transition-all duration-700 ${
                  isActive 
                    ? 'border-[#fecd02]/80 shadow-2xl shadow-[#fecd02]/20 bg-gradient-to-br from-[#1F2029]/80 to-[#1F2029]/90' 
                    : 'border-[#fecd02]/20 hover:border-[#fecd02]/50 hover:shadow-xl hover:shadow-[#fecd02]/10'
                }`}
                style={{
                  transformStyle: "preserve-3d",
                  perspective: "1000px"
                }}
              >
                {/* Efecto de brillo animado */}
                <motion.div
                  className="absolute inset-0 bg-gradient-to-r from-transparent via-[#fecd02]/10 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-700 rounded-3xl"
                  animate={isActive ? {
                    x: ["-100%", "100%"]
                  } : {}}
                  transition={{
                    duration: 3,
                    repeat: isActive ? Infinity : 0,
                    repeatType: "loop",
                    ease: "linear"
                  }}
                />

                {/* Icono mejorado */}
                <motion.div
                  whileHover={{ scale: 1.2, rotate: 5 }}
                  className={`w-20 h-20 rounded-3xl flex items-center justify-center mb-8 transition-all duration-500 ${
                    isActive 
                      ? 'bg-[#fecd02]/30 shadow-2xl shadow-[#fecd02]/40 border border-[#fecd02]/50' 
                      : 'bg-[#fecd02]/10 border border-[#fecd02]/20 group-hover:bg-[#fecd02]/20 group-hover:border-[#fecd02]/40'
                  }`}
                >
                  <IconComponent className={`w-10 h-10 ${isActive ? 'text-[#fecd02]' : 'text-[#fecd02] group-hover:scale-110'} transition-all duration-300`} />
                </motion.div>

                {/* Contenido */}
                <motion.h3 
                  className={`text-3xl font-bold mb-6 transition-all duration-500 ${
                    isActive ? 'text-[#fecd02]' : 'text-white group-hover:text-[#fecd02]'
                  }`}
                  whileHover={{ scale: 1.05 }}
                >
                  {feature.title}
                </motion.h3>
                
                <p className={`mb-8 text-lg leading-relaxed transition-all duration-500 ${
                  isActive ? 'text-gray-200' : 'text-gray-300 group-hover:text-gray-200'
                }`}>
                  {feature.description}
                </p>

                {/* Lista de beneficios */}
                <ul className="space-y-4 mb-8">
                  {feature.benefits.map((benefit, benefitIndex) => (
                    <motion.li
                      key={benefitIndex}
                      initial={{ opacity: 0, x: -30 }}
                      animate={isInView ? { opacity: 1, x: 0 } : {}}
                      transition={{ delay: index * 0.15 + benefitIndex * 0.1 + 0.8 }}
                      className={`flex items-center text-lg transition-all duration-500 ${
                        isActive ? 'text-gray-200' : 'text-gray-300 group-hover:text-gray-200'
                      }`}
                    >
                      <motion.div
                        whileHover={{ scale: 1.5, rotate: 180 }}
                        className={`w-3 h-3 bg-[#fecd02] rounded-full mr-4 transition-all duration-500 ${
                          isActive ? 'shadow-lg shadow-[#fecd02]/60' : 'group-hover:shadow-lg group-hover:shadow-[#fecd02]/60'
                        }`}
                      />
                      {benefit}
                    </motion.li>
                  ))}
                </ul>

               

                {/* Indicador de número mejorado */}
                <motion.div
                  className={`absolute -top-6 -right-6 w-16 h-16 bg-[#fecd02] rounded-full flex items-center justify-center text-[#1F2029] font-bold text-xl transition-all duration-500 shadow-2xl ${
                    isActive ? 'opacity-100 scale-110' : 'opacity-0 group-hover:opacity-100 group-hover:scale-100'
                  }`}
                  whileHover={{ scale: 1.3, rotate: 360 }}
                  transition={{ duration: 0.6 }}
                >
                  {index + 1}
                </motion.div>

                {/* Efecto de selección */}
                {isActive && (
                  <motion.div
                    className="absolute inset-0 border-3 border-[#fecd02] rounded-3xl"
                    initial={{ scale: 0.8, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    transition={{ duration: 0.4 }}
                  />
                )}

                {/* Partículas flotantes */}
                {isActive && (
                  <div className="absolute inset-0 pointer-events-none">
                    {[...Array(6)].map((_, i) => (
                      <motion.div
                        key={i}
                        className="absolute w-1 h-1 bg-[#fecd02] rounded-full"
                        style={{
                          left: `${Math.random() * 100}%`,
                          top: `${Math.random() * 100}%`,
                        }}
                        animate={{
                          y: [-20, -60, -20],
                          opacity: [0, 1, 0],
                          scale: [0, 1, 0],
                        }}
                        transition={{
                          duration: 3,
                          repeat: Infinity,
                          delay: i * 0.5,
                          ease: "easeInOut",
                        }}
                      />
                    ))}
                  </div>
                )}
              </motion.div>
            );
          })}
        </motion.div>
      </div>
    </section>
  );
};

export default FeaturesSection;

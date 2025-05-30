import React from 'react';
import { motion } from 'framer-motion';
import { useInView } from 'framer-motion';
import { useRef } from 'react';
import { Target, Eye, Users, Lightbulb } from 'lucide-react';

const AboutSection = () => {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: "-100px" });

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.2,
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

  const aboutItems = [
    {
      title: "Misión",
      icon: Target,
      content: "Democratizar el acceso a herramientas financieras avanzadas, permitiendo que cualquier persona o empresa gestione sus recursos de manera inteligente, segura y eficiente.",
    },
    {
      title: "Visión",
      icon: Eye,
      content: "Ser la plataforma líder en gestión financiera inteligente en Colombia, transformando la manera en que las personas y empresas toman decisiones financieras.",
    },
    {
      title: "¿Quiénes Somos?",
      icon: Users,
      content: "Somos un equipo de expertos en tecnología financiera, comprometidos con la innovación y la seguridad. Combinamos experiencia en finanzas, tecnología y diseño UX.",
    },
    {
      title: "¿Qué Queremos?",
      icon: Lightbulb,
      content: "Queremos empoderar a nuestros usuarios con herramientas que les permitan tomar control total de sus finanzas, planificar su futuro y alcanzar sus metas económicas.",
    }
  ];

  return (
    <section id="about" ref={ref} className="py-32 px-6 relative overflow-hidden bg-gradient-to-br from-[rgba(31,32,41,1)] via-[rgba(31,32,41,0.95)] to-[rgba(31,32,41,1)]">
      {/* Efectos de fondo */}
      <div className="absolute inset-0">
        <motion.div 
          className="absolute top-1/4 left-0 w-96 h-96 bg-[#fecd02]/5 rounded-full blur-3xl"
          animate={{
            scale: [1, 1.2, 1],
            x: [0, 50, 0],
            opacity: [0.05, 0.1, 0.05],
          }}
          transition={{
            duration: 8,
            repeat: Infinity,
            ease: "easeInOut",
          }}
        />
        <motion.div 
          className="absolute bottom-1/4 right-0 w-96 h-96 bg-[#fecd02]/8 rounded-full blur-3xl"
          animate={{
            scale: [1.2, 1, 1.2],
            x: [0, -50, 0],
            opacity: [0.08, 0.03, 0.08],
          }}
          transition={{
            duration: 10,
            repeat: Infinity,
            ease: "easeInOut",
            delay: 4,
          }}
        />
      </div>

      <div className="max-w-7xl mx-auto relative z-10">
        {/* Header */}
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
                "0 0 20px rgba(254, 205, 2, 0.2)",
                "0 0 40px rgba(254, 205, 2, 0.4)",
                "0 0 20px rgba(254, 205, 2, 0.2)",
              ],
            }}
            transition={{
              duration: 4,
              repeat: Infinity,
              ease: "easeInOut",
            }}
          >
            Sobre <span className="text-[#fecd02] drop-shadow-2xl">Nosotros</span>
          </motion.h2>
          <motion.p 
            className="text-2xl text-gray-300 max-w-4xl mx-auto leading-relaxed"
            initial={{ opacity: 0 }}
            animate={isInView ? { opacity: 1 } : {}}
            transition={{ delay: 0.5, duration: 1 }}
          >
            Conoce más sobre nuestra misión, visión y el equipo detrás de Saving Secure
          </motion.p>
        </motion.div>

        {/* Grid de información */}
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate={isInView ? "visible" : "hidden"}
          className="grid md:grid-cols-2 gap-10 mb-20"
        >
          {aboutItems.map((item, index) => {
            const IconComponent = item.icon;
            
            return (
              <motion.div
                key={index}
                variants={itemVariants}
                whileHover={{ 
                  scale: 1.03,
                  rotateY: 3,
                  z: 50
                }}
                className="relative group bg-gradient-to-br from-[#1F2029]/60 via-[#1F2029]/70 to-[#1F2029]/80 backdrop-blur-xl border-2 border-[#fecd02]/20 rounded-3xl p-10 hover:border-[#fecd02]/50 transition-all duration-700"
                style={{
                  transformStyle: "preserve-3d",
                  perspective: "1000px"
                }}
              >
                {/* Efecto de brillo */}
                <motion.div
                  className="absolute inset-0 bg-gradient-to-r from-transparent via-[#fecd02]/8 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-700 rounded-3xl"
                  animate={{
                    x: ["-100%", "100%"]
                  }}
                  transition={{
                    duration: 3,
                    repeat: Infinity,
                    repeatType: "loop",
                    ease: "linear"
                  }}
                />

                {/* Icono */}
                <motion.div
                  whileHover={{ scale: 1.2, rotate: 10 }}
                  className="w-20 h-20 rounded-3xl flex items-center justify-center mb-8 bg-[#fecd02]/20 border border-[#fecd02]/30 group-hover:bg-[#fecd02]/30 group-hover:border-[#fecd02]/60 transition-all duration-500"
                >
                  <IconComponent className="w-10 h-10 text-[#fecd02]" />
                </motion.div>
                
                <motion.h3 
                  className="text-4xl font-bold mb-8 text-[#fecd02] group-hover:text-[#e6b800] transition-colors duration-500"
                  whileHover={{ scale: 1.05 }}
                >
                  {item.title}
                </motion.h3>
                
                <p className="text-gray-300 text-lg leading-relaxed group-hover:text-white transition-colors duration-500">
                  {item.content}
                </p>

                {/* Indicador flotante */}
                <motion.div
                  className="absolute -top-6 -right-6 w-16 h-16 bg-[#fecd02] rounded-full flex items-center justify-center text-[#1F2029] font-bold text-xl opacity-0 group-hover:opacity-100 transition-all duration-500 shadow-2xl"
                  whileHover={{ scale: 1.3, rotate: 360 }}
                  transition={{ duration: 0.6 }}
                >
                  {index + 1}
                </motion.div>
              </motion.div>
            );
          })}
        </motion.div>
      </div>
    </section>
  );
};

export default AboutSection;

import React, { useState, useRef } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { useInView } from 'framer-motion';
import { Mail, Phone, MapPin, Send, CheckCircle, AlertCircle } from 'lucide-react';
import { saveFeedback } from '../services/feedbackService';

const ContactSection = () => {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: "-100px" });
  const [formData, setFormData] = useState({
    nombre: '',
    email: '',
    sugerencia: ''
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitStatus, setSubmitStatus] = useState<'idle' | 'success' | 'error'>('idle');
  const [errors, setErrors] = useState<{ [key: string]: string }>({});
  const [rating, setRating] = useState(0);
  const [hoveredRating, setHoveredRating] = useState(0);

  const validateForm = () => {
    const newErrors: { [key: string]: string } = {};
    
    if (!formData.nombre.trim()) {
      newErrors.nombre = 'El nombre es requerido';
    }
    
    if (!formData.email.trim()) {
      newErrors.email = 'El email es requerido';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'El formato del email no es válido';
    }
    
    if (!formData.sugerencia.trim()) {
      newErrors.sugerencia = 'El mensaje es requerido';
    }

    if (rating === 0) {
      newErrors.rating = 'Por favor, selecciona una calificación';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }));
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) return;
    
    setIsSubmitting(true);
    
    try {
      const result = await saveFeedback({
        ...formData,
        rating
      });

      if (result.success) {
        setSubmitStatus('success');
        setFormData({ nombre: '', email: '', sugerencia: '' });
        setRating(0);
      } else {
        setSubmitStatus('error');
      }
    } catch (error) {
      setSubmitStatus('error');
    } finally {
      setIsSubmitting(false);
      setTimeout(() => setSubmitStatus('idle'), 5000);
    }
  };

  const contactInfo = [
    {
      icon: Mail,
      title: "Email",
      details: ["info@savingsecure.com", "soporte@savingsecure.com"],
      gradient: "from-[#fecd02]/10 to-[#ffd700]/10"
    },
    {
      icon: Phone, 
      title: "Teléfono",
      details: ["+57 302 349 8419", "WhatsApp disponible"],
      gradient: "from-[#fecd02]/10 to-[#ffd700]/10"
    },
    {
      icon: MapPin,
      title: "Dirección",
      details: ["SENA Centro de Industria", "Ibagué, Colombia"],
      gradient: "from-[#fecd02]/10 to-[#ffd700]/10"
    }
  ];

  return (
    <section id="contact" ref={ref} className="py-20 px-6 relative overflow-hidden">
      {/* Efectos de fondo */}
      <div className="absolute inset-0">
        <div className="absolute top-1/4 left-0 w-96 h-96 bg-[#fecd02]/10 rounded-full blur-3xl"></div>
        <div className="absolute bottom-1/4 right-0 w-96 h-96 bg-[#fecd02]/10 rounded-full blur-3xl"></div>
      </div>

      <div className="max-w-7xl mx-auto relative z-10">
        {/* Header */}
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          animate={isInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 1 }}
          className="text-center mb-20"
        >
          <h2 className="text-6xl md:text-7xl font-bold mb-6 text-white">
            Conecta con <span className="text-[#fecd02]">Nosotros</span>
          </h2>
          <p className="text-xl text-gray-300 max-w-3xl mx-auto">
            ¿Listo para transformar tu gestión financiera? Contáctanos y descubre todo lo que podemos hacer por ti
          </p>
        </motion.div>

        {/* Opinion Section */}
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          animate={isInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 1, delay: 0.3 }}
          className="mb-20"
        >
          <div className="relative">
            {/* Efectos de fondo para la sección */}
            <div className="absolute -top-20 left-1/4 w-64 h-64 bg-[#fecd02]/5 rounded-full blur-3xl"></div>
            <div className="absolute -bottom-20 right-1/4 w-64 h-64 bg-[#fecd02]/5 rounded-full blur-3xl"></div>
            
            <div className="bg-gradient-to-br from-gray-800/30 to-gray-900/30 rounded-3xl p-12 max-w-3xl mx-auto backdrop-blur-sm border border-gray-700/30 hover:border-[#fecd02]/30 transition-all duration-500 relative overflow-hidden group">
              {/* Efecto de brillo en hover */}
              <div className="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                <div className="absolute inset-0 bg-gradient-to-r from-transparent via-[#fecd02]/5 to-transparent animate-shimmer"></div>
              </div>
              
              <div className="text-center relative z-10">
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  animate={isInView ? { opacity: 1, y: 0 } : {}}
                  transition={{ delay: 0.4 }}
                >
                  <h2 className="text-5xl font-bold mb-6 bg-gradient-to-r from-[#fecd02] to-yellow-500 bg-clip-text text-transparent">
                    TU OPINIÓN
                  </h2>
                  <p className="text-xl text-gray-300 mb-12">
                    ¿Qué te ha parecido nuestra plataforma?
                  </p>
                </motion.div>
                
                <div className="flex justify-center items-center space-x-8">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <motion.button
                      key={star}
                      whileHover={{ scale: 1.2, rotate: 12 }}
                      whileTap={{ scale: 0.8 }}
                      initial={{ opacity: 0, y: 20 }}
                      animate={isInView ? { opacity: 1, y: 0 } : {}}
                      transition={{ 
                        duration: 0.3, 
                        delay: 0.5 + star * 0.1,
                        type: "spring",
                        stiffness: 200
                      }}
                      onClick={() => setRating(star)}
                      onMouseEnter={() => setHoveredRating(star)}
                      onMouseLeave={() => setHoveredRating(0)}
                      className={`relative group/star transform transition-all duration-300 focus:outline-none`}
                    >
                      {/* Efecto de brillo detrás de la estrella */}
                      <div className={`absolute inset-0 rounded-full blur-xl transition-opacity duration-300 ${
                        star <= (hoveredRating || rating) ? 'bg-[#fecd02]/30 opacity-100' : 'opacity-0'
                      }`}></div>
                      
                      <svg
                        className={`w-16 h-16 transition-all duration-300 ${
                          star <= (hoveredRating || rating) 
                            ? 'text-[#fecd02] drop-shadow-[0_0_8px_rgba(254,205,2,0.5)]' 
                            : 'text-gray-600'
                        }`}
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                      
                      {/* Tooltip con el número */}
                      <div className={`absolute -top-8 left-1/2 -translate-x-1/2 transition-all duration-300 ${
                        star === hoveredRating ? 'opacity-100 transform-none' : 'opacity-0 translate-y-2'
                      }`}>
                        <span className="bg-[#fecd02] text-black px-2 py-1 rounded-md text-sm font-semibold">
                          {star}
                        </span>
                      </div>
                    </motion.button>
                  ))}
                </div>
                
                <AnimatePresence>
                  {rating > 0 && (
                    <motion.div
                      initial={{ opacity: 0, y: 20 }}
                      animate={{ opacity: 1, y: 0 }}
                      exit={{ opacity: 0, y: -20 }}
                      transition={{ duration: 0.5, type: "spring" }}
                      className="mt-12"
                    >
                      <p className="text-2xl font-bold bg-gradient-to-r from-[#fecd02] to-yellow-500 bg-clip-text text-transparent">
                        ¡Gracias por tu valoración!
                      </p>
                      <motion.p 
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ delay: 0.3 }}
                        className="text-gray-300 mt-2"
                      >
                        Tu opinión nos ayuda a mejorar
                      </motion.p>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            </div>
          </div>
        </motion.div>

        <div className="grid lg:grid-cols-2 gap-12">
          {/* Información de contacto */}
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            animate={isInView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 1, delay: 0.2 }}
            className="space-y-8"
          >
            <h3 className="text-4xl font-bold text-white mb-8">Información de Contacto</h3>
            
            {contactInfo.map((info, index) => {
              const IconComponent = info.icon;
              return (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 30 }}
                  animate={isInView ? { opacity: 1, y: 0 } : {}}
                  transition={{ duration: 0.8, delay: 0.4 + index * 0.2 }}
                  whileHover={{ scale: 1.02, x: 10 }}
                  className={`bg-gradient-to-br ${info.gradient} backdrop-blur-sm border border-gray-700/30 hover:border-[#fecd02]/30 rounded-2xl p-6 transition-all duration-500 group hover:bg-[#fecd02]/5`}
                >
                  <div className="flex items-start space-x-4">
                    <motion.div
                      whileHover={{ scale: 1.2, rotate: 10 }}
                      className="w-14 h-14 bg-[#fecd02]/10 rounded-xl flex items-center justify-center group-hover:bg-[#fecd02]/20 transition-all duration-300"
                    >
                      <IconComponent className="w-7 h-7 text-[#fecd02]" />
                    </motion.div>
                    <div>
                      <h4 className="text-white font-semibold text-xl mb-2 group-hover:text-[#fecd02] transition-colors duration-300">
                        {info.title}
                      </h4>
                      {info.details.map((detail, detailIndex) => (
                        <p key={detailIndex} className="text-gray-300 group-hover:text-white transition-colors duration-300">
                          {detail}
                        </p>
                      ))}
                    </div>
                  </div>
                </motion.div>
              );
            })}
          </motion.div>

          {/* Formulario de contacto */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            animate={isInView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 1, delay: 0.4 }}
          >
            <form onSubmit={handleSubmit} className="bg-gradient-to-br from-gray-800/30 to-gray-900/30 p-8 rounded-3xl backdrop-blur-sm border border-gray-700/30 hover:border-[#fecd02]/30 transition-all duration-500">
              <h3 className="text-3xl font-bold mb-8 text-white">Envíanos una Sugerencia</h3>
              
              <div className="space-y-6">
                {/* Campo Nombre */}
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  animate={isInView ? { opacity: 1, y: 0 } : {}}
                  transition={{ delay: 0.6 }}
                >
                  <label className="block text-gray-300 mb-2 font-medium">Nombre *</label>
                  <motion.input
                    whileFocus={{ scale: 1.02 }}
                    type="text"
                    name="nombre"
                    value={formData.nombre}
                    onChange={handleInputChange}
                    className={`w-full bg-gray-800/50 border ${errors.nombre ? 'border-red-500' : 'border-gray-600/50'} rounded-xl px-4 py-4 text-white focus:border-[#fecd02] focus:outline-none focus:ring-2 focus:ring-[#fecd02]/20 transition-all duration-300`}
                    placeholder="Tu nombre completo"
                  />
                  {errors.nombre && (
                    <motion.p
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      className="text-red-400 text-sm mt-1 flex items-center"
                    >
                      <AlertCircle className="w-4 h-4 mr-1" />
                      {errors.nombre}
                    </motion.p>
                  )}
                </motion.div>

                {/* Campo Email */}
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  animate={isInView ? { opacity: 1, y: 0 } : {}}
                  transition={{ delay: 0.7 }}
                >
                  <label className="block text-gray-300 mb-2 font-medium">Email *</label>
                  <motion.input
                    whileFocus={{ scale: 1.02 }}
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleInputChange}
                    className={`w-full bg-gray-800/50 border ${errors.email ? 'border-red-500' : 'border-gray-600/50'} rounded-xl px-4 py-4 text-white focus:border-[#fecd02] focus:outline-none focus:ring-2 focus:ring-[#fecd02]/20 transition-all duration-300`}
                    placeholder="tu@email.com"
                  />
                  {errors.email && (
                    <motion.p
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      className="text-red-400 text-sm mt-1 flex items-center"
                    >
                      <AlertCircle className="w-4 h-4 mr-1" />
                      {errors.email}
                    </motion.p>
                  )}
                </motion.div>

                {/* Campo Mensaje */}
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  animate={isInView ? { opacity: 1, y: 0 } : {}}
                  transition={{ delay: 0.8 }}
                >
                  <label className="block text-gray-300 mb-2 font-medium">Mensaje *</label>
                  <motion.textarea
                    whileFocus={{ scale: 1.02 }}
                    name="sugerencia"
                    value={formData.sugerencia}
                    onChange={handleInputChange}
                    rows={4}
                    className={`w-full bg-gray-800/50 border ${errors.sugerencia ? 'border-red-500' : 'border-gray-600/50'} rounded-xl px-4 py-4 text-white focus:border-[#fecd02] focus:outline-none focus:ring-2 focus:ring-[#fecd02]/20 transition-all duration-300 resize-none`}
                    placeholder="Cuéntanos cómo podemos ayudarte..."
                  />
                  {errors.sugerencia && (
                    <motion.p
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      className="text-red-400 text-sm mt-1 flex items-center"
                    >
                      <AlertCircle className="w-4 h-4 mr-1" />
                      {errors.sugerencia}
                    </motion.p>
                  )}
                </motion.div>

                {/* Mensaje de error para el rating */}
                {errors.rating && (
                  <motion.p
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    className="text-red-400 text-sm mt-1 flex items-center"
                  >
                    <AlertCircle className="w-4 h-4 mr-1" />
                    {errors.rating}
                  </motion.p>
                )}

                {/* Botón de envío */}
                <motion.button
                  initial={{ opacity: 0, y: 20 }}
                  animate={isInView ? { opacity: 1, y: 0 } : {}}
                  transition={{ delay: 0.9 }}
                  type="submit"
                  disabled={isSubmitting}
                  whileHover={{ scale: isSubmitting ? 1 : 1.05 }}
                  whileTap={{ scale: isSubmitting ? 1 : 0.95 }}
                  className={`w-full py-4 rounded-xl font-bold text-lg transition-all duration-300 flex items-center justify-center space-x-2 ${
                    isSubmitting
                      ? 'bg-gray-600 text-gray-400 cursor-not-allowed'
                      : submitStatus === 'success'
                      ? 'bg-green-600 text-white'
                      : submitStatus === 'error'
                      ? 'bg-red-600 text-white'
                      : 'bg-[#fecd02] text-black hover:bg-yellow-400 hover:shadow-2xl hover:shadow-[#fecd02]/25'
                  }`}
                >
                  {isSubmitting ? (
                    <>
                      <motion.div
                        animate={{ rotate: 360 }}
                        transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
                        className="w-5 h-5 border-2 border-gray-400 border-t-transparent rounded-full"
                      />
                      <span>Enviando...</span>
                    </>
                  ) : submitStatus === 'success' ? (
                    <>
                      <CheckCircle className="w-5 h-5" />
                      <span>¡Mensaje Enviado!</span>
                    </>
                  ) : submitStatus === 'error' ? (
                    <>
                      <AlertCircle className="w-5 h-5" />
                      <span>Error al Enviar</span>
                    </>
                  ) : (
                    <>
                      <Send className="w-5 h-5" />
                      <span>Enviar Mensaje</span>
                    </>
                  )}
                </motion.button>
              </div>
            </form>
          </motion.div>
        </div>
      </div>
    </section>
  );
};

export default ContactSection;

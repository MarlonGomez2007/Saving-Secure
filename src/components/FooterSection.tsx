import React from 'react';
import { motion } from 'framer-motion';
import { Facebook, Instagram, Linkedin, Youtube, Mail, Phone, MapPin, Shield, Award, Zap, Calculator, PieChart, TrendingUp } from 'lucide-react';
import FooterParticles from './FooterParticles';

const FooterSection = () => {
  const socialLinks = [
    { Icon: Instagram, href: 'https://www.instagram.com/saving._.secure?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==', label: 'Instagram', color: 'hover:text-pink-400' },
    
  ];

  const quickLinks = [
    { name: 'Inicio', href: '#hero' },
    { name: 'Nosotros', href: '#about' },
    { name: 'Servicios', href: '#features' },
    { name: 'Casos de Uso', href: '#usecases' },
    { name: 'Contacto', href: '#contact' }
  ];

  const legalLinks = [
    { text: 'Términos y Condiciones', path: '/assets/documentos/terminos de usos.pdf' },
    { text: 'Política de Privacidad', path: '/assets/documentos/Politica de Privacidad Saving Secure.pdf' },
    { text: 'Política de Cookies', path: '/assets/documentos/cookies.pdf' }
  ];

  const handleDocumentClick = (documentPath: string) => {
    const baseUrl = window.location.origin;
    window.open(`${baseUrl}${documentPath}`, '_blank');
  };

  const features = [
    { Icon: Calculator, text: 'Control de Gastos' },
    { Icon: PieChart, text: 'Reportes Detallados' },
    { Icon: TrendingUp, text: 'Análisis Financiero' }
  ];

  return (
    <footer className="relative bg-gradient-to-b from-[rgba(31,32,41,0.95)] to-[rgba(31,32,41,1)] backdrop-blur-xl border-t border-[#fecd02]/30 overflow-hidden">
      <FooterParticles />
      {/* Efectos de fondo mejorados */}
      <div className="absolute inset-0">
        <div className="absolute top-0 left-1/4 w-96 h-96 bg-[#fecd02]/10 rounded-full blur-3xl animate-pulse"></div>
        <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-[#fecd02]/8 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[800px] h-[400px] bg-gradient-to-r from-[#fecd02]/5 to-transparent rounded-full blur-3xl"></div>
      </div>

      {/* Patrón de puntos decorativo */}
      <div className="absolute inset-0 opacity-20">
        <div className="absolute inset-0" style={{
          backgroundImage: `radial-gradient(circle at 2px 2px, #fecd02 1px, transparent 0)`,
          backgroundSize: '40px 40px'
        }}></div>
      </div>

      <div className="max-w-7xl mx-auto relative z-10 px-6 py-20">
        <div className="grid lg:grid-cols-4 md:grid-cols-2 gap-12 mb-16">
          {/* Brand Section Mejorada */}
          <motion.div
            initial={{ opacity: 0, y: 50 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            className="lg:col-span-2"
          >
            {/* Logo con efectos mejorados */}
            <motion.div
              whileHover={{ scale: 1.05 }}
              className="flex items-center space-x-4 mb-8"
            >
              <motion.div
                whileHover={{ rotate: 360 }}
                transition={{ duration: 0.8 }}
                className="relative w-20 h-20"
              >
                <img src="/assets/img/logo.png" alt="Saving Secure Logo" className="w-full h-full object-contain" />
              </motion.div>
              <div>
                <h3 className="text-4xl font-bold text-white mb-1">
                  Saving <span className="text-[#fecd02]">Secure</span>
                </h3>
              </div>
            </motion.div>

            <motion.p
              initial={{ opacity: 0 }}
              whileInView={{ opacity: 1 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="text-gray-300 mb-8 max-w-lg leading-relaxed text-lg"
            >
              <span className="text-[#fecd02] font-semibold">Innovación en Finanzas Personales.</span> 
              {" "}La fintech colombiana que revoluciona la gestión financiera personal y empresarial 
              con tecnología de vanguardia y máxima seguridad.
            </motion.p>

            {/* Features destacados */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
              {features.map(({ Icon, text }, index) => (
                <motion.div
                  key={text}
                  initial={{ opacity: 0, scale: 0.8 }}
                  whileInView={{ opacity: 1, scale: 1 }}
                  transition={{ duration: 0.5, delay: 0.3 + index * 0.1 }}
                  className="flex items-center space-x-2 p-3 rounded-xl bg-[rgba(31,32,41,0.8)] border border-[#fecd02]/20 hover:border-[#fecd02]/50 transition-all duration-300"
                >
                  <Icon size={20} className="text-[#fecd02]" />
                  <span className="text-gray-300 text-sm font-medium">{text}</span>
                </motion.div>
              ))}
            </div>
            
            {/* Redes sociales mejoradas */}
            <div className="flex space-x-4">
              {socialLinks.map(({ Icon, href, label, color }, index) => (
                <motion.a
                  key={label}
                  href={href}
                  initial={{ opacity: 0, scale: 0 }}
                  whileInView={{ opacity: 1, scale: 1 }}
                  transition={{ duration: 0.5, delay: 0.6 + index * 0.1 }}
                  whileHover={{ 
                    scale: 1.2, 
                    rotate: 10,
                    y: -8
                  }}
                  whileTap={{ scale: 0.9 }}
                  className={`relative w-16 h-16 bg-gradient-to-br from-[rgba(31,32,41,0.8)] to-[rgba(31,32,41,0.6)] rounded-2xl flex items-center justify-center text-gray-400 ${color} transition-all duration-300 border border-[#fecd02]/30 hover:border-[#fecd02]/70 group overflow-hidden`}
                  aria-label={label}
                >
                  <div className="absolute inset-0 bg-gradient-to-br from-[#fecd02]/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                  <Icon size={26} className="relative z-10 group-hover:scale-110 transition-transform duration-300" />
                </motion.a>
              ))}
            </div>
          </motion.div>

          {/* Enlaces Rápidos Mejorados */}
          <motion.div
            initial={{ opacity: 0, y: 50 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2 }}
            className="relative"
          >
            <div className="absolute -top-4 -left-4 w-24 h-24 bg-[#fecd02]/5 rounded-full blur-xl"></div>
            <h4 className="text-white font-bold mb-8 text-2xl flex items-center">
              <span className="w-2 h-8 bg-gradient-to-b from-[#fecd02] to-yellow-500 rounded-full mr-3"></span>
              Enlaces Rápidos
            </h4>
            <ul className="space-y-5">
              {quickLinks.map((link, index) => (
                <motion.li
                  key={link.name}
                  initial={{ opacity: 0, x: -20 }}
                  whileInView={{ opacity: 1, x: 0 }}
                  transition={{ duration: 0.5, delay: 0.4 + index * 0.1 }}
                >
                  <motion.a
                    href={link.href}
                    whileHover={{ x: 15, color: '#fecd02' }}
                    className="text-gray-300 hover:text-[#fecd02] transition-all duration-300 inline-block relative group text-lg font-medium"
                  >
                    <span className="relative z-10">{link.name}</span>
                    <span className="absolute -bottom-1 left-0 w-0 h-1 bg-gradient-to-r from-[#fecd02] to-yellow-500 transition-all duration-300 group-hover:w-full rounded-full"></span>
                    <span className="absolute -left-2 top-1/2 transform -translate-y-1/2 w-1 h-1 bg-[#fecd02] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
                  </motion.a>
                </motion.li>
              ))}
            </ul>
          </motion.div>

          {/* Información Legal Mejorada */}
          <motion.div
            initial={{ opacity: 0, y: 50 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.4 }}
            className="relative"
          >
            <div className="absolute -top-4 -right-4 w-24 h-24 bg-[#fecd02]/5 rounded-full blur-xl"></div>
            <h4 className="text-white font-bold mb-8 text-2xl flex items-center">
              <span className="w-2 h-8 bg-gradient-to-b from-[#fecd02] to-yellow-500 rounded-full mr-3"></span>
              Legal & Seguridad
            </h4>
            <ul className="space-y-4">
              {legalLinks.map((link, index) => (
                <motion.li
                  key={link.text}
                  initial={{ opacity: 0, x: -20 }}
                  whileInView={{ opacity: 1, x: 0 }}
                  transition={{ duration: 0.5, delay: 0.6 + index * 0.1 }}
                >
                  <motion.button
                    onClick={() => handleDocumentClick(link.path)}
                    whileHover={{ x: 15, color: '#fecd02' }}
                    className="text-gray-300 hover:text-[#fecd02] transition-all duration-300 inline-block relative group bg-transparent border-0 cursor-pointer"
                  >
                    <span className="relative z-10">{link.text}</span>
                    <span className="absolute -bottom-1 left-0 w-0 h-0.5 bg-gradient-to-r from-[#fecd02] to-yellow-500 transition-all duration-300 group-hover:w-full"></span>
                  </motion.button>
                </motion.li>
              ))}
            </ul>

            {/* Información de contacto */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.8 }}
              className="mt-8 p-4 rounded-xl bg-gradient-to-br from-[rgba(31,32,41,0.8)] to-[rgba(31,32,41,0.6)] border border-[#fecd02]/20"
            >
              <div className="space-y-3">
                <div className="flex items-center space-x-3 text-gray-300">
                  <Mail size={16} className="text-[#fecd02]" />
                  <span className="text-sm">info@savingsecure.com</span>
                </div>
                <div className="flex items-center space-x-3 text-gray-300">
                  <Phone size={16} className="text-[#fecd02]" />
                  <span className="text-sm">+57 3023498419</span>
                </div>
                <div className="flex items-center space-x-3 text-gray-300">
                  <MapPin size={16} className="text-[#fecd02]" />
                  <span className="text-sm">Ibagué, Colombia</span>
                </div>
              </div>
            </motion.div>
          </motion.div>
        </div>

        {/* Bottom Section Mejorada */}
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.6 }}
          className="pt-8 border-t border-gradient-to-r from-transparent via-[#fecd02]/30 to-transparent"
        >
          <div className="flex flex-col lg:flex-row justify-between items-center space-y-6 lg:space-y-0">
            <div className="text-center lg:text-left">
              <p className="text-gray-400 text-lg mb-2">
                © 2025 <span className="text-[#fecd02] font-semibold">Saving Secure</span>. Todos los derechos reservados.
              </p>
              
            </div>
            
            <motion.div
              whileHover={{ scale: 1.05 }}
              className="flex items-center space-x-6"
            >
              <div className="text-center">
                
                <motion.div
                  whileHover={{ rotate: 5, scale: 1.1 }}
                  className="relative w-16 h-16 mx-auto"
                >
                  
                </motion.div>
              </div>
            </motion.div>
          </div>
        </motion.div>
      </div>

      {/* Línea decorativa final */}
      <div className="h-1 bg-gradient-to-r from-transparent via-[#fecd02] to-transparent"></div>
    </footer>
  );
};

export default FooterSection;

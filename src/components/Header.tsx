import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Menu, X, ChevronRight, Home, Users, Phone } from 'lucide-react';

const Header = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [visible, setVisible] = useState(true);
  const [prevScrollPos, setPrevScrollPos] = useState(0);

  useEffect(() => {
    const handleScroll = () => {
      const currentScrollPos = window.scrollY;
      
      // Determinar si debe mostrarse basado en la dirección del scroll
      setVisible(prevScrollPos > currentScrollPos || currentScrollPos < 50);
      setScrolled(currentScrollPos > 50);
      
      setPrevScrollPos(currentScrollPos);
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, [prevScrollPos]);

  const navItems = [
    { name: 'Sobre Nosotros', href: '#about', icon: Users },
    { name: 'Usos', href: '#features', icon: Home },
    { name: 'Contacto', href: '#contact', icon: Phone }
  ];

  const menuVariants = {
    closed: {
      opacity: 0,
      x: '100%',
      transition: {
        type: "spring",
        stiffness: 300,
        damping: 30
      }
    },
    open: {
      opacity: 1,
      x: 0,
      transition: {
        type: "spring",
        stiffness: 300,
        damping: 30
      }
    }
  };

  const itemVariants = {
    closed: { x: 20, opacity: 0 },
    open: (i) => ({
      x: 0,
      opacity: 1,
      transition: {
        delay: i * 0.1,
        type: "spring",
        stiffness: 300,
        damping: 30
      }
    })
  };

  return (
    <motion.header
      initial={{ y: -100 }}
      animate={{ y: visible ? 0 : -100 }}
      transition={{ duration: 0.3 }}
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-500 ${
        scrolled ? 'bg-[#1a1b26]/95 backdrop-blur-md shadow-lg' : 'bg-transparent'
      }`}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 py-4">
        <div className="flex items-center justify-between">
          {/* Logo */}
          <motion.div
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
            className="flex items-center space-x-3"
          >
            <div className="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center">
              <img src="/assets/img/logo.png" alt="Saving Secure Logo" className="w-full h-full object-contain" />
            </div>
            <div>
              <h1 className="text-white font-bold text-lg sm:text-xl">Saving Secure</h1>
              <p className="text-[#fecd02] text-xs">Gestión Financiera</p>
            </div>
          </motion.div>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center space-x-8">
            {navItems.map((item, index) => (
              <motion.a
                key={item.name}
                href={item.href}
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: index * 0.1 }}
                whileHover={{ y: -2 }}
                className="text-gray-300 hover:text-[#fecd02] transition-all duration-300 relative group"
              >
                {item.name}
                <span className="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#fecd02] transition-all duration-300 group-hover:w-full"></span>
              </motion.a>
            ))}
            <motion.button
              whileHover={{ scale: 1.05 }}
              whileTap={{ scale: 0.95 }}
              onClick={() => window.location.href = '/login.html'}
              className="bg-[#fecd02] text-black px-6 py-2 rounded-full font-semibold hover:bg-yellow-400 transition-all duration-300"
            >
              Iniciar Sesión
            </motion.button>
          </nav>

          {/* Mobile Menu Button */}
          <motion.button
            whileTap={{ scale: 0.9 }}
            onClick={() => setIsMenuOpen(!isMenuOpen)}
            className="md:hidden text-white p-2"
          >
            {isMenuOpen ? <X size={24} /> : <Menu size={24} />}
          </motion.button>
        </div>

        {/* Mobile Menu */}
        <AnimatePresence>
          {isMenuOpen && (
            <>
              {/* Backdrop */}
              <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                onClick={() => setIsMenuOpen(false)}
                className="fixed inset-0 bg-black/60 backdrop-blur-sm md:hidden z-50"
              />
              
              {/* Menu Panel */}
              <motion.div
                variants={menuVariants}
                initial="closed"
                animate="open"
                exit="closed"
                className="fixed top-0 right-0 w-[85%] h-full bg-[#1a1b26]/98 backdrop-blur-lg shadow-2xl md:hidden z-50 overflow-hidden"
              >
                <div className="relative p-6">
                  {/* Close button */}
                  <motion.button
                    whileHover={{ scale: 1.1 }}
                    whileTap={{ scale: 0.9 }}
                    onClick={() => setIsMenuOpen(false)}
                    className="absolute top-4 right-4 text-white/80 p-2 hover:text-white transition-colors"
                  >
                    <X size={24} />
                  </motion.button>

                  {/* Menu content */}
                  <div className="space-y-8">
                    {/* Logo section */}
                    <motion.div
                      variants={itemVariants}
                      custom={0}
                      className="flex items-center space-x-4 mb-8"
                    >
                      <motion.div
                        whileHover={{ scale: 1.05 }}
                        className="w-12 h-12 rounded-2xl flex items-center justify-center bg-black/20"
                      >
                        <img src="/assets/img/logo.png" alt="Saving Secure Logo" className="w-8 h-8 object-contain" />
                      </motion.div>
                      <div>
                        <h2 className="text-white font-bold text-xl">Saving Secure</h2>
                        <p className="text-[#fecd02] text-sm">Gestión Financiera</p>
                      </div>
                    </motion.div>

                    {/* Navigation Items */}
                    <div className="space-y-3">
                      {navItems.map((item, index) => {
                        const Icon = item.icon;
                        return (
                          <motion.div
                            key={item.name}
                            variants={itemVariants}
                            custom={index + 1}
                          >
                            <motion.a
                              href={item.href}
                              onClick={() => setIsMenuOpen(false)}
                              whileHover={{ x: 5 }}
                              className="flex items-center w-full p-4 rounded-2xl bg-black/20 hover:bg-black/30 transition-all duration-300"
                            >
                              <Icon className="text-[#fecd02] w-5 h-5 mr-3" />
                              <span className="text-white/90 text-base font-medium flex-grow">
                                {item.name}
                              </span>
                              <ChevronRight 
                                className="text-white/40" 
                                size={18} 
                              />
                            </motion.a>
                          </motion.div>
                        );
                      })}

                      {/* Login Button */}
                      <motion.div
                        variants={itemVariants}
                        custom={navItems.length + 1}
                      >
                        <motion.button
                          whileTap={{ scale: 0.98 }}
                          onClick={() => window.location.href = '/login.html'}
                          className="w-full bg-[#fecd02] text-black py-3.5 rounded-2xl font-semibold text-base"
                        >
                          Iniciar Sesión
                        </motion.button>
                      </motion.div>
                    </div>
                  </div>
                </div>
              </motion.div>
            </>
          )}
        </AnimatePresence>
      </div>
    </motion.header>
  );
};

export default Header;

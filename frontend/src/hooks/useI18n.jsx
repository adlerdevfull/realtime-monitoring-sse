import { createContext, useContext, useMemo, useState, useCallback } from 'react'

const messages = {
  "en": {
    "appName": "Realtime Monitor",
    "appTagline": "SSE event stream",
    "logout": "Log out",
    "language": "Language",
    "login": {
      "title": "Realtime Monitor",
      "subtitle": "SSE event stream",
      "email": "Email",
      "password": "Password",
      "submit": "Sign in",
      "loading": "Signing in…",
      "error": "Invalid credentials"
    },
    "nav": {
      "home": "Dashboard",
      "events": "Events"
    },
    "dashboard": "Dashboard"
  },
  "es": {
    "appName": "Monitor en Tiempo Real",
    "appTagline": "Stream SSE de eventos",
    "logout": "Cerrar sesión",
    "language": "Idioma",
    "login": {
      "title": "Monitor en Tiempo Real",
      "subtitle": "Stream SSE de eventos",
      "email": "Email",
      "password": "Contraseña",
      "submit": "Iniciar sesión",
      "loading": "Entrando…",
      "error": "Credenciales inválidas"
    },
    "nav": {
      "home": "Dashboard",
      "events": "Eventos"
    },
    "dashboard": "Dashboard"
  },
  "pt": {
    "appName": "Monitor em Tempo Real",
    "appTagline": "Stream SSE de eventos",
    "logout": "Sair",
    "language": "Idioma",
    "login": {
      "title": "Monitor em Tempo Real",
      "subtitle": "Stream SSE de eventos",
      "email": "Email",
      "password": "Senha",
      "submit": "Entrar",
      "loading": "Entrando…",
      "error": "Credenciais inválidas"
    },
    "nav": {
      "home": "Dashboard",
      "events": "Eventos"
    },
    "dashboard": "Dashboard"
  }
}

const I18nContext = createContext(null)

function detect() {
  const saved = localStorage.getItem('locale')
  if (saved && messages[saved]) return saved
  const nav = (navigator.language || 'en').slice(0, 2)
  if (nav === 'es' || nav === 'pt') return nav
  return 'en'
}

export function I18nProvider({ children }) {
  const [locale, setLocaleState] = useState(detect)
  const setLocale = useCallback((next) => {
    setLocaleState(next)
    localStorage.setItem('locale', next)
    document.documentElement.lang = next
  }, [])
  const t = useCallback((key) => {
    const parts = key.split('.')
    let cur = messages[locale]
    for (const p of parts) cur = cur?.[p]
    return typeof cur === 'string' ? cur : key
  }, [locale])
  const value = useMemo(() => ({ locale, setLocale, t, messages: messages[locale] }), [locale, setLocale, t])
  return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>
}

export function useI18n() {
  const ctx = useContext(I18nContext)
  if (!ctx) throw new Error('useI18n must be used within I18nProvider')
  return ctx
}

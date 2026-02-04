# SCRIPT DIAGNÓSTICO Y CORRECCIÓN - ASSETS DINÁMICOS
# Problema: ContadoresTable-BBgU-2Fk.js retorna 404 en producción
# Fecha: 02-03-2026

Write-Host "=== DIAGNÓSTICO COMPLETO DE PROBLEMA ASSETS DINÁMICOS ===" -ForegroundColor Yellow
Write-Host "Fecha: $(Get-Date)" -ForegroundColor Gray
Write-Host

# 1. Verificar archivos compilados existen
Write-Host "1. VERIFICANDO ARCHIVOS COMPILADOS:" -ForegroundColor Blue
Write-Host "   ContadoresTable-BBgU-2Fk.js existe:"
if (Test-Path "public\build\assets\ContadoresTable-BBgU-2Fk.js") {
    $fileInfo = Get-ChildItem "public\build\assets\ContadoresTable-BBgU-2Fk.js"
    Write-Host "   ✅ SÍ - Tamaño: $($fileInfo.Length) bytes, Modificado: $($fileInfo.LastWriteTime)" -ForegroundColor Green
} else {
    Write-Host "   ❌ NO - Archivo no encontrado" -ForegroundColor Red
}

Write-Host "   Manifest.json existe:"
if (Test-Path "public\build\manifest.json") {
    $manifestInfo = Get-ChildItem "public\build\manifest.json"
    Write-Host "   ✅ SÍ - Tamaño: $($manifestInfo.Length) bytes" -ForegroundColor Green
    
    Write-Host "   Contenido manifest para ContadoresTable:"
    $manifestContent = Get-Content "public\build\manifest.json" -Raw
    if ($manifestContent -match "ContadoresTable") {
        $manifestContent | Select-String -Pattern "ContadoresTable.*?" -AllMatches | ForEach-Object { 
            Write-Host "   📄 $_" -ForegroundColor Cyan 
        }
    } else {
        Write-Host "   ❌ No encontrado en manifest" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ NO - Manifest no encontrado" -ForegroundColor Red
}

Write-Host
Write-Host "2. VERIFICANDO CONFIGURACIÓN DE ENTORNO:" -ForegroundColor Blue
if (Test-Path ".env") {
    Write-Host "   APP_URL en .env:"
    $appUrl = Select-String -Path ".env" -Pattern "APP_URL"
    if ($appUrl) {
        Write-Host "   📄 $($appUrl.Line)" -ForegroundColor Cyan
    } else {
        Write-Host "   ❌ APP_URL no encontrado" -ForegroundColor Red
    }

    Write-Host "   APP_ENV:"
    $appEnv = Select-String -Path ".env" -Pattern "APP_ENV"
    if ($appEnv) {
        Write-Host "   📄 $($appEnv.Line)" -ForegroundColor Cyan
    } else {
        Write-Host "   ❌ APP_ENV no encontrado" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ .env no encontrado" -ForegroundColor Red
}

Write-Host
Write-Host "3. VERIFICANDO CONFIGURACIÓN VITE:" -ForegroundColor Blue
if (Test-Path "vite.config.js") {
    Write-Host "   ✅ vite.config.js existe" -ForegroundColor Green
    $viteConfig = Get-Content "vite.config.js" -Raw
    if ($viteConfig -match "base|publicPath") {
        Write-Host "   Configuración base URL encontrada" -ForegroundColor Cyan
    } else {
        Write-Host "   No configuración de base URL específica" -ForegroundColor Yellow
    }
} else {
    Write-Host "   ❌ vite.config.js no encontrado" -ForegroundColor Red
}

Write-Host
Write-Host "4. VERIFICANDO ESTRUCTURA /public/build/:" -ForegroundColor Blue
if (Test-Path "public\build\assets\") {
    $assetFiles = Get-ChildItem "public\build\assets\"
    Write-Host "   Total archivos en assets: $($assetFiles.Count)" -ForegroundColor Cyan
    
    $jsFiles = $assetFiles | Where-Object { $_.Extension -eq ".js" } | Select-Object -First 10
    Write-Host "   Archivos JS compilados (primeros 10):"
    $jsFiles | ForEach-Object { Write-Host "   📄 $($_.Name)" -ForegroundColor Gray }
} else {
    Write-Host "   ❌ Directorio assets no encontrado" -ForegroundColor Red
}

Write-Host
Write-Host "5. VERIFICANDO PERMISOS:" -ForegroundColor Blue
if (Test-Path "public\build\") {
    $buildDir = Get-ChildItem "public\build\" -Directory
    Write-Host "   ✅ Directorio build accesible" -ForegroundColor Green
} else {
    Write-Host "   ❌ Directorio build no accesible" -ForegroundColor Red
}

Write-Host
Write-Host "6. PROBANDO ACCESO DIRECTO A ARCHIVO:" -ForegroundColor Blue
if (Test-Path "public\build\assets\ContadoresTable-BBgU-2Fk.js") {
    try {
        $content = Get-Content "public\build\assets\ContadoresTable-BBgU-2Fk.js" -TotalCount 3 -ErrorAction Stop
        Write-Host "   ✅ Archivo es legible" -ForegroundColor Green
        Write-Host "   Primeras líneas:" -ForegroundColor Gray
        $content | ForEach-Object { Write-Host "   $($_)" -ForegroundColor Gray }
    } catch {
        Write-Host "   ❌ Error leyendo archivo: $_" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ Archivo no es legible" -ForegroundColor Red
}

Write-Host
Write-Host "=== RECOMENDACIONES ===" -ForegroundColor Yellow
Write-Host
Write-Host "A. Si archivos existen pero no cargan en producción:" -ForegroundColor White
Write-Host "   1. Verificar configuración servidor web (Apache/Nginx)" -ForegroundColor Gray
Write-Host "   2. Verificar APP_URL en producción: https://new.grupoatlantiscrm.eu" -ForegroundColor Gray
Write-Host "   3. Limpiar caché Laravel: php artisan cache:clear" -ForegroundColor Gray
Write-Host "   4. Limpiar caché config: php artisan config:clear" -ForegroundColor Gray
Write-Host "   5. Verificar .htaccess o nginx.conf para servir archivos estáticos" -ForegroundColor Gray

Write-Host
Write-Host "B. Si archivos no existen:" -ForegroundColor White
Write-Host "   1. Ejecutar: npm run build" -ForegroundColor Gray
Write-Host "   2. Verificar package.json scripts" -ForegroundColor Gray
Write-Host "   3. Revisar vite.config.js" -ForegroundColor Gray

Write-Host
Write-Host "C. Para producción CRÍTICO:" -ForegroundColor Red
Write-Host "   1. APP_URL debe ser: https://new.grupoatlantiscrm.eu" -ForegroundColor White
Write-Host "   2. APP_ENV debe ser: production" -ForegroundColor White  
Write-Host "   3. Ejecutar: php artisan config:cache" -ForegroundColor White
Write-Host "   4. Verificar permisos servidor web en /public/build/" -ForegroundColor White
Write-Host "   5. Verificar que servidor web sirve archivos de /build/assets/" -ForegroundColor White

Write-Host
Write-Host "=== COMANDOS DE CORRECCIÓN INMEDIATA ===" -ForegroundColor Magenta
Write-Host "# En el servidor de producción:" -ForegroundColor Gray
Write-Host "npm run build" -ForegroundColor Yellow
Write-Host "php artisan cache:clear" -ForegroundColor Yellow
Write-Host "php artisan config:clear" -ForegroundColor Yellow  
Write-Host "php artisan config:cache" -ForegroundColor Yellow
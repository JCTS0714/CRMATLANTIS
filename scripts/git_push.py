import subprocess, os
repo = r'C:\xampp\htdocs\CRMATLANTIS'
os.chdir(repo)

def run(cmd):
    print('> ' + cmd)
    p = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    if p.stdout:
        print(p.stdout)
    if p.stderr:
        print(p.stderr)
    return p.returncode

run('git status --porcelain')
run('git add -A')
ret = run('git commit -m "Ajuste: usar logo_alta_calidad.png en pantalla de login (reemplazo avatar)"')
if ret != 0:
    print('Commit exited with code', ret)
run('git push')

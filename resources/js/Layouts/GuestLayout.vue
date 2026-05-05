<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-base font-sans selection:bg-accent/10 selection:text-accent flex items-center justify-center p-6 relative overflow-hidden">
        <!-- Abstract Background Orbs -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-accent/5 rounded-full blur-[120px] animate-float"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px] animate-float" style="animation-delay: -2s"></div>

        <div class="w-full max-w-[440px] relative z-10">
            <!-- Brand -->
            <div class="flex flex-col items-center mb-12">
                <Link href="/" class="group">
                    <div class="w-16 h-16 bg-primary rounded-[2rem] flex items-center justify-center shadow-2xl shadow-black/20 group-hover:scale-110 transition-all duration-500 group-hover:rotate-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </Link>
                <div class="mt-8 text-center space-y-2">
                    <h1 class="text-4xl font-display font-black tracking-tight text-primary uppercase">Reworks</h1>
                    <p class="text-[10px] font-black text-accent uppercase tracking-[0.4em]">System Intelligence v2.0</p>
                </div>
            </div>

            <!-- Login Card -->
            <div class="card-base bg-white/70 backdrop-blur-2xl border-white/40 shadow-premium p-10 md:p-12">
                <slot />
            </div>

            <!-- Footer -->
            <div class="mt-12 text-center">
                <p class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em]">Authorized Personnel Only</p>
                <div class="mt-4 flex items-center justify-center gap-4">
                    <div class="h-[1px] w-8 bg-zinc-200"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-zinc-200"></div>
                    <div class="h-[1px] w-8 bg-zinc-200"></div>
                </div>
            </div>
        </div>
    </div>
</template>

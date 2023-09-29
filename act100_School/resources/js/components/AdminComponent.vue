<template>
    <v-app id="app">
        <!-- <v-navigation-drawer v-model="drawer" clipped fixed app > -->
        <v-navigation-drawer v-model="" clipped fixed app >
            <v-list dense>
                <r-link linkname='home'></r-link>
                <r-link linkname='admin_user'></r-link>
                <r-link linkname='admin_payslip'></r-link>
    <!-- ★      <r-link linkname='admin_actlog'></r-link> -->
            </v-list>
        </v-navigation-drawer>
    </v-app>

    <v-toolbar color="primary" dark fixed app clipped-left></v-toolbar>
</template>